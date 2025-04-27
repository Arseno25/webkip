<?php

namespace App\Helpers;

class KMeansHelper
{
  public static function initializeCentroids($data, $k, $method = 'rata-rata')
  {
    if (empty($data)) return [];

    switch ($method) {
      case 'random':
        return self::randomCentroids($data, $k);
      case 'first-k':
        return array_slice($data, 0, $k);
      case 'rata-rata':
      default:
        return self::averageBasedCentroids($data, $k);
    }
  }

  private static function randomCentroids($data, $k)
  {
    $keys = array_rand($data, $k);
    $centroids = [];
    foreach ((array)$keys as $key) {
      $centroids[] = $data[$key];
    }
    return $centroids;
  }

  private static function averageBasedCentroids($data, $k)
  {
    $n = count($data);
    $dim = count($data[0]);
    $min = array_fill(0, $dim, PHP_FLOAT_MAX);
    $max = array_fill(0, $dim, PHP_FLOAT_MIN);

    // Find min and max for each dimension
    foreach ($data as $point) {
      for ($i = 0; $i < $dim; $i++) {
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

  public static function kmeans($data, $k, $maxIterations = 100, $centroidMethod = 'rata-rata')
  {
    if (empty($data)) return ['centroids' => [], 'clusters' => [], 'iterations' => 0, 'history' => []];

    $centroids = self::initializeCentroids($data, $k, $centroidMethod);
    $prevCentroids = [];
    $iterations = 0;
    $history = [];

    while (!self::centroidsConverged($centroids, $prevCentroids) && $iterations < $maxIterations) {
      $prevCentroids = $centroids;
      $clusters = array_fill(0, $k, []);
      $distances = [];

      // Assign points to nearest centroid & simpan distance
      foreach ($data as $idx => $point) {
        $minDist = PHP_FLOAT_MAX;
        $cluster = 0;
        $pointDistances = [];
        for ($i = 0; $i < $k; $i++) {
          $dist = self::euclideanDistance($point, $centroids[$i]);
          $pointDistances[] = $dist;
          if ($dist < $minDist) {
            $minDist = $dist;
            $cluster = $i;
          }
        }
        $clusters[$cluster][] = $point;
        $distances[$idx] = $pointDistances;
      }

      // Simpan history
      $history[] = [
        'centroids' => $centroids,
        'distances' => $distances,
      ];

      // Update centroids
      for ($i = 0; $i < $k; $i++) {
        if (!empty($clusters[$i])) {
          $centroids[$i] = self::calculateMean($clusters[$i]);
        }
      }

      $iterations++;
    }

    return [
      'centroids' => $centroids,
      'clusters' => $clusters,
      'iterations' => $iterations,
      'history' => $history,
    ];
  }

  public static function euclideanDistance($a, $b)
  {
    return sqrt(array_sum(array_map(function ($x, $y) {
      return pow($x - $y, 2);
    }, $a, $b)));
  }

  private static function calculateMean($points)
  {
    $n = count($points);
    if ($n === 0) return [];

    $dim = count($points[0]);
    $mean = array_fill(0, $dim, 0);

    foreach ($points as $point) {
      for ($i = 0; $i < $dim; $i++) {
        $mean[$i] += $point[$i];
      }
    }

    for ($i = 0; $i < $dim; $i++) {
      $mean[$i] /= $n;
    }

    return $mean;
  }

  private static function centroidsConverged($current, $previous)
  {
    if (empty($previous)) return false;
    $threshold = 0.0001;

    foreach ($current as $i => $centroid) {
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
