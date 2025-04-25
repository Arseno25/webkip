<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subdistrict;

class SubdistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Contoh data kecamatan dengan boundaries GeoJSON
        $subdistricts = [
            [
                'name' => 'Kecamatan A',
                'code' => 'KEC-A',
                'district' => 'Kabupaten X',
                'province' => 'Provinsi Y',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'boundaries' => json_encode([
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => [
                            [
                                [106.8356, -6.2188],
                                [106.8556, -6.2188],
                                [106.8556, -6.1988],
                                [106.8356, -6.1988],
                                [106.8356, -6.2188]
                            ]
                        ]
                    ],
                    'properties' => [
                        'name' => 'Kecamatan A'
                    ]
                ])
            ],
            [
                'name' => 'Kecamatan B',
                'code' => 'KEC-B',
                'district' => 'Kabupaten X',
                'province' => 'Provinsi Y',
                'latitude' => -6.3088,
                'longitude' => 106.7456,
                'boundaries' => json_encode([
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => [
                            [
                                [106.7356, -6.3188],
                                [106.7556, -6.3188],
                                [106.7556, -6.2988],
                                [106.7356, -6.2988],
                                [106.7356, -6.3188]
                            ]
                        ]
                    ],
                    'properties' => [
                        'name' => 'Kecamatan B'
                    ]
                ])
            ],
            [
                'name' => 'Kecamatan C',
                'code' => 'KEC-C',
                'district' => 'Kabupaten Z',
                'province' => 'Provinsi Y',
                'latitude' => -6.1088,
                'longitude' => 106.9456,
                'boundaries' => json_encode([
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => [
                            [
                                [106.9356, -6.1188],
                                [106.9556, -6.1188],
                                [106.9556, -6.0988],
                                [106.9356, -6.0988],
                                [106.9356, -6.1188]
                            ]
                        ]
                    ],
                    'properties' => [
                        'name' => 'Kecamatan C'
                    ]
                ])
            ]
        ];

        foreach ($subdistricts as $subdistrict) {
            Subdistrict::create($subdistrict);
        }
    }
}
