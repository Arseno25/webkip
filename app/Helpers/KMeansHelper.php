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

  public static function kmeans($data, $k, $maxIterations = 100, $initMethod = 'kmeans++')
  {
    if (empty($data)) {
      throw new \Exception('Data tidak boleh kosong.');
    }

    $n = count($data);
    if ($n < 2) {
      throw new \Exception('Minimal harus ada 2 data untuk melakukan clustering.');
    }

    // Jika hanya ada 2 data, paksa menggunakan k=2
    if ($n == 2) {
      $k = 2;
    }
    // Jika k terlalu besar, sesuaikan
    else if ($k >= $n) {
      $k = max(2, $n - 1);
    }

    // Inisialisasi centroid
    try {
      $centroids = $initMethod === 'kmeans++' ?
        self::initializeKMeansPlusPlusCentroids($data, $k) :
        self::initializeRandomCentroids($data, $k);
    } catch (\Exception $e) {
      // Jika gagal dengan kmeans++, coba dengan random
      $centroids = self::initializeRandomCentroids($data, $k);
    }

    $prevCentroids = [];
    $iterations = 0;
    $convergenceThreshold = 0.0001;

    while ($iterations < $maxIterations) {
      // Assign points to clusters
      $clusters = array_fill(0, $k, []);
      foreach ($data as $i => $point) {
        $bestCluster = 0;
        $minDistance = PHP_FLOAT_MAX;

        for ($j = 0; $j < $k; $j++) {
          $distance = self::euclideanDistance($point, $centroids[$j]);
          if ($distance < $minDistance) {
            $minDistance = $distance;
            $bestCluster = $j;
          }
        }
        $clusters[$bestCluster][] = $i;
      }

      // Save previous centroids
      $prevCentroids = $centroids;

      // Update centroids
      for ($i = 0; $i < $k; $i++) {
        if (empty($clusters[$i])) {
          // Jika cluster kosong, pilih data secara acak
          $randomIndex = array_rand($data);
          $centroids[$i] = $data[$randomIndex];
          continue;
        }

        $newCentroid = array_fill_keys(array_keys($data[0]), 0.0);
        foreach ($clusters[$i] as $pointIndex) {
          foreach ($data[$pointIndex] as $feature => $value) {
            $newCentroid[$feature] += $value;
          }
        }
        foreach ($newCentroid as $feature => $sum) {
          $newCentroid[$feature] = $sum / count($clusters[$i]);
        }
        $centroids[$i] = $newCentroid;
      }

      // Check convergence
      if (self::hasConverged($prevCentroids, $centroids, $convergenceThreshold)) {
        break;
      }

      $iterations++;
    }

    return [
      'clusters' => $clusters,
      'centroids' => $centroids,
      'iterations' => $iterations
    ];
  }

  private static function initializeKMeansPlusPlusCentroids($data, $k)
  {
    $centroids = [];
    $n = count($data);

    // Choose first centroid randomly
    $firstIndex = rand(0, $n - 1);
    $centroids[] = $data[$firstIndex];

    // Choose remaining centroids
    while (count($centroids) < $k) {
      $distances = [];
      $sumDistances = 0;

      // Calculate distances from points to nearest centroid
      foreach ($data as $point) {
        $minDistance = PHP_FLOAT_MAX;
        foreach ($centroids as $centroid) {
          $distance = self::euclideanDistance($point, $centroid);
          $minDistance = min($minDistance, $distance);
        }
        $distances[] = $minDistance * $minDistance;
        $sumDistances += $distances[count($distances) - 1];
      }

      // Choose next centroid with probability proportional to distance
      $rand = mt_rand() / mt_getrandmax() * $sumDistances;
      $sum = 0;
      foreach ($distances as $i => $distance) {
        $sum += $distance;
        if ($sum >= $rand) {
          $centroids[] = $data[$i];
          break;
        }
      }
    }

    return $centroids;
  }

  private static function initializeRandomCentroids($data, $k)
  {
    $n = count($data);

    // Jika k lebih besar dari jumlah data, kurangi k
    if ($k >= $n) {
      $k = max(2, $n - 1);
    }

    // Jika hanya ada 2 data, gunakan keduanya sebagai centroid
    if ($n == 2 && $k == 2) {
      return $data;
    }

    // Acak indeks untuk centroid
    $indices = range(0, $n - 1);
    shuffle($indices);
    $indices = array_slice($indices, 0, $k);

    // Buat centroid dari indeks yang dipilih
    $centroids = [];
    foreach ($indices as $index) {
      $centroids[] = $data[$index];
    }

    return $centroids;
  }

  private static function euclideanDistance($point1, $point2)
  {
    if (count($point1) !== count($point2)) {
      throw new \Exception('Points have different dimensions');
    }

    $sum = 0;
    foreach ($point1 as $key => $value) {
      $diff = $value - $point2[$key];
      $sum += $diff * $diff;
    }
    return sqrt($sum);
  }

  private static function hasConverged($oldCentroids, $newCentroids, $threshold)
  {
    if (empty($oldCentroids)) return false;

    foreach ($newCentroids as $i => $centroid) {
      if (self::euclideanDistance($centroid, $oldCentroids[$i]) > $threshold) {
        return false;
      }
    }
    return true;
  }

  public static function calculateWCSS($data, $clusters, $centroids)
  {
    $wcss = 0;
    foreach ($clusters as $i => $cluster) {
      foreach ($cluster as $pointIndex) {
        $wcss += pow(self::euclideanDistance($data[$pointIndex], $centroids[$i]), 2);
      }
    }
    return $wcss;
  }

  public static function calculateSilhouetteScore($data, $clusters)
  {
    if (count($clusters) < 2) {
      return 0;
    }

    $silhouetteSum = 0;
    $totalPoints = 0;

    foreach ($clusters as $i => $cluster) {
      foreach ($cluster as $pointIndex) {
        $a = self::calculateAverageIntraClusterDistance($data, $pointIndex, $cluster);
        $b = self::calculateMinInterClusterDistance($data, $pointIndex, $clusters, $i);

        if ($a === 0 && $b === 0) {
          continue;
        }

        $silhouette = ($b - $a) / max($a, $b);
        $silhouetteSum += $silhouette;
        $totalPoints++;
      }
    }

    return $totalPoints > 0 ? $silhouetteSum / $totalPoints : 0;
  }

  private static function calculateAverageIntraClusterDistance($data, $pointIndex, $cluster)
  {
    if (count($cluster) <= 1) return 0;

    $sum = 0;
    foreach ($cluster as $otherIndex) {
      if ($pointIndex !== $otherIndex) {
        $sum += self::euclideanDistance($data[$pointIndex], $data[$otherIndex]);
      }
    }
    return $sum / (count($cluster) - 1);
  }

  private static function calculateMinInterClusterDistance($data, $pointIndex, $clusters, $currentClusterIndex)
  {
    $minDistance = PHP_FLOAT_MAX;

    foreach ($clusters as $i => $cluster) {
      if ($i !== $currentClusterIndex && !empty($cluster)) {
        $distance = 0;
        foreach ($cluster as $otherIndex) {
          $distance += self::euclideanDistance($data[$pointIndex], $data[$otherIndex]);
        }
        $avgDistance = $distance / count($cluster);
        $minDistance = min($minDistance, $avgDistance);
      }
    }

    return $minDistance === PHP_FLOAT_MAX ? 0 : $minDistance;
  }
}
