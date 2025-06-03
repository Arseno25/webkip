<?php

namespace App\Helpers;

class KMeansHelper
{
  public static function normalizeData($data)
  {
    if (empty($data)) return [];

    // Pastikan semua data memiliki dimensi yang sama
    $dim = count($data[0]);
    foreach ($data as $point) {
      if (count($point) !== $dim) {
        throw new \Exception('All data points must have the same dimensions');
      }
    }

    $min = array_fill(0, $dim, PHP_FLOAT_MAX);
    $max = array_fill(0, $dim, PHP_FLOAT_MIN);

    // Find min and max for each dimension
    foreach ($data as $point) {
      for ($i = 0; $i < $dim; $i++) {
        if (!isset($point[$i])) {
          throw new \Exception('Invalid data point: missing dimension ' . $i);
        }
        $min[$i] = min($min[$i], $point[$i]);
        $max[$i] = max($max[$i], $point[$i]);
      }
    }

    // Normalize data
    $normalized = [];
    foreach ($data as $point) {
      $normPoint = [];
      for ($i = 0; $i < $dim; $i++) {
        $range = $max[$i] - $min[$i];
        $normPoint[] = $range == 0 ? 0 : ($point[$i] - $min[$i]) / $range;
      }
      $normalized[] = $normPoint;
    }

    return $normalized;
  }

  public static function initializeCentroids($data, $k, $method = 'random')
  {
    if (empty($data)) return [];
    if ($k <= 0) return [];

    $n = count($data);
    if ($n === 0) return [];

    $dim = count($data[0]);
    if ($dim === 0) return [];

    switch ($method) {
      case 'random':
        return self::randomCentroids($data, $k);
      case 'first_k':
        return array_slice($data, 0, min($k, $n));
      case 'average':
      default:
        return self::averageBasedCentroids($data, $k);
    }
  }

  private static function randomCentroids($data, $k)
  {
    $n = count($data);
    if ($n === 0) return [];
    if ($k <= 0) return [];

    // Jika k lebih besar dari jumlah data, gunakan semua data yang ada
    if ($k >= $n) {
      return $data;
    }

    // Pilih k data secara acak
    $keys = array_rand($data, $k);
    $centroids = [];
    foreach ((array)$keys as $key) {
      if (isset($data[$key])) {
        $centroids[] = $data[$key];
      }
    }
    return $centroids;
  }

  private static function averageBasedCentroids($data, $k)
  {
    if (empty($data)) return [];
    if ($k <= 0) return [];

    $n = count($data);
    $dim = count($data[0]);

    if ($n === 0 || $dim === 0) return [];

    $min = array_fill(0, $dim, PHP_FLOAT_MAX);
    $max = array_fill(0, $dim, PHP_FLOAT_MIN);

    // Find min and max for each dimension
    foreach ($data as $point) {
      for ($i = 0; $i < $dim; $i++) {
        if (!isset($point[$i])) continue;
        $min[$i] = min($min[$i], $point[$i]);
        $max[$i] = max($max[$i], $point[$i]);
      }
    }

    // Create centroids evenly spaced
    $centroids = [];
    for ($i = 0; $i < $k; $i++) {
      $centroid = [];
      for ($j = 0; $j < $dim; $j++) {
        $centroid[] = $min[$j] + ($max[$j] - $min[$j]) * ($i + 1) / ($k + 1);
      }
      $centroids[] = $centroid;
    }
    return $centroids;
  }

  public static function kmeans($data, $k, $maxIterations = 100, $centroidMethod = 'random')
  {
    if (empty($data)) return ['centroids' => [], 'clusters' => [], 'iterations' => 0, 'history' => []];
    if ($k <= 0) return ['centroids' => [], 'clusters' => [], 'iterations' => 0, 'history' => []];

    // Normalize data
    $normalizedData = self::normalizeData($data);
    if (empty($normalizedData)) return ['centroids' => [], 'clusters' => [], 'iterations' => 0, 'history' => []];

    // Initialize centroids
    $centroids = self::initializeCentroids($normalizedData, $k, $centroidMethod);
    if (empty($centroids)) return ['centroids' => [], 'clusters' => [], 'iterations' => 0, 'history' => []];

    $prevCentroids = [];
    $iterations = 0;
    $history = [];

    while (!self::centroidsConverged($centroids, $prevCentroids) && $iterations < $maxIterations) {
      $prevCentroids = $centroids;
      $clusters = array_fill(0, $k, []);
      $distances = [];

      // Assign points to nearest centroid
      foreach ($normalizedData as $idx => $point) {
        $minDist = PHP_FLOAT_MAX;
        $cluster = 0;
        $pointDistances = [];

        for ($i = 0; $i < $k; $i++) {
          if (!isset($centroids[$i])) continue;
          $dist = self::euclideanDistance($point, $centroids[$i]);
          $pointDistances[] = $dist;
          if ($dist < $minDist) {
            $minDist = $dist;
            $cluster = $i;
          }
        }

        if (isset($clusters[$cluster])) {
          $clusters[$cluster][] = $point;
        }
        $distances[$idx] = $pointDistances;
      }

      // Save history
      $history[] = [
        'centroids' => $centroids,
        'distances' => $distances,
      ];

      // Update centroids
      for ($i = 0; $i < $k; $i++) {
        if (!empty($clusters[$i])) {
          $centroids[$i] = self::calculateMean($clusters[$i]);
        } else {
          // If cluster is empty, reinitialize its centroid
          $newCentroid = self::initializeCentroids($normalizedData, 1, 'random');
          if (!empty($newCentroid)) {
            $centroids[$i] = $newCentroid[0];
          }
        }
      }

      $iterations++;
    }

    // Denormalize centroids back to original scale
    $denormalizedCentroids = self::denormalizeCentroids($centroids, $data);

    return [
      'centroids' => $denormalizedCentroids,
      'clusters' => $clusters,
      'iterations' => $iterations,
      'history' => $history,
    ];
  }

  private static function denormalizeCentroids($centroids, $originalData)
  {
    if (empty($centroids) || empty($originalData)) return $centroids;

    $dim = count($originalData[0]);
    $min = array_fill(0, $dim, PHP_FLOAT_MAX);
    $max = array_fill(0, $dim, PHP_FLOAT_MIN);

    // Find min and max for each dimension
    foreach ($originalData as $point) {
      for ($i = 0; $i < $dim; $i++) {
        if (!isset($point[$i])) continue;
        $min[$i] = min($min[$i], $point[$i]);
        $max[$i] = max($max[$i], $point[$i]);
      }
    }

    // Denormalize centroids
    $denormalized = [];
    foreach ($centroids as $centroid) {
      $denormCentroid = [];
      for ($i = 0; $i < $dim; $i++) {
        if (!isset($centroid[$i])) continue;
        $range = $max[$i] - $min[$i];
        $denormCentroid[] = $centroid[$i] * $range + $min[$i];
      }
      $denormalized[] = $denormCentroid;
    }

    return $denormalized;
  }

  public static function euclideanDistance($a, $b)
  {
    if (count($a) !== count($b)) {
      throw new \Exception('Dimensions do not match');
    }

    $sum = 0;
    for ($i = 0; $i < count($a); $i++) {
      if (!isset($a[$i]) || !isset($b[$i])) continue;
      $sum += pow($a[$i] - $b[$i], 2);
    }
    return sqrt($sum);
  }

  private static function calculateMean($points)
  {
    if (empty($points)) return [];

    $dim = count($points[0]);
    $mean = array_fill(0, $dim, 0);
    $count = array_fill(0, $dim, 0);

    foreach ($points as $point) {
      for ($i = 0; $i < $dim; $i++) {
        if (!isset($point[$i])) continue;
        $mean[$i] += $point[$i];
        $count[$i]++;
      }
    }

    for ($i = 0; $i < $dim; $i++) {
      if ($count[$i] > 0) {
        $mean[$i] /= $count[$i];
      }
    }

    return $mean;
  }

  private static function centroidsConverged($current, $previous)
  {
    if (empty($previous)) return false;
    $threshold = 0.0001;

    foreach ($current as $i => $centroid) {
      if (!isset($previous[$i])) return false;
      if (self::euclideanDistance($centroid, $previous[$i]) > $threshold) {
        return false;
      }
    }
    return true;
  }

  public static function calculateWCSS($clusters, $centroids)
  {
    $wcss = 0;
    foreach ($clusters as $i => $cluster) {
      if (!isset($centroids[$i])) continue;
      foreach ($cluster as $point) {
        $wcss += pow(self::euclideanDistance($point, $centroids[$i]), 2);
      }
    }
    return $wcss;
  }

  public static function calculateSilhouetteScore($data, $clusters)
  {
    if (count($clusters) < 2) return 0;

    $silhouettes = [];
    foreach ($clusters as $i => $cluster) {
      foreach ($cluster as $point) {
        $a = self::averageIntraClusterDistance($point, $cluster);
        $b = self::minimumInterClusterDistance($point, $clusters, $i);

        if (max($a, $b) == 0) {
          $silhouettes[] = 0;
        } else {
          $silhouettes[] = ($b - $a) / max($a, $b);
        }
      }
    }

    return !empty($silhouettes) ? array_sum($silhouettes) / count($silhouettes) : 0;
  }

  private static function averageIntraClusterDistance($point, $cluster)
  {
    if (count($cluster) <= 1) return 0;

    $sum = 0;
    $count = 0;
    foreach ($cluster as $other) {
      if ($point !== $other) {
        $sum += self::euclideanDistance($point, $other);
        $count++;
      }
    }
    return $count > 0 ? $sum / $count : 0;
  }

  private static function minimumInterClusterDistance($point, $clusters, $currentClusterIndex)
  {
    $minDist = PHP_FLOAT_MAX;

    foreach ($clusters as $i => $cluster) {
      if ($i !== $currentClusterIndex && !empty($cluster)) {
        $avgDist = array_sum(array_map(function ($other) use ($point) {
          return self::euclideanDistance($point, $other);
        }, $cluster)) / count($cluster);

        $minDist = min($minDist, $avgDist);
      }
    }

    return $minDist === PHP_FLOAT_MAX ? 0 : $minDist;
  }
}
