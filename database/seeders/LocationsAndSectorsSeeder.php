<?php
// ============================================================================
// Seeders to import Sierra Leone locations + sectors from CSV files
// Tables (pre-existing): location_regions, location_districts, location_chiefdoms, location_councils
// Also seeds: sectors
// Place your CSVs here: database/seeders/data/
//   - regions.csv      (headers: code,name)
//   - districts.csv    (headers: code,name,region_code)
//   - chiefdoms.csv    (headers: name,district_code)
//   - councils.csv     (headers: name,type,district_code|region_code)
//   - sector.csv       (headers: code,name)
// Run: php artisan db:seed --class=Database\Seeders\LocationsAndSectorsSeeder
// ============================================================================

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/** Small CSV helper */
trait ReadsCsv
{
    protected function csv(string $path): array
    {
        if (!File::exists($path)) return [];
        $rows = array_map('str_getcsv', file($path));
        if (!$rows) return [];
        $headers = array_map(fn($h)=>Str::of($h)->trim()->lower()->value(), array_shift($rows));
        return array_map(function($row) use ($headers) {
            $row = array_map(fn($v)=>is_string($v)?trim($v):$v, $row);
            return array_combine($headers, $row);
        }, $rows);
    }

    protected function dataPath(string $file): string
    {
        return base_path('database/seeders/data/'.$file);
    }
}

class LocationsAndSectorsSeeder extends Seeder
{
    use ReadsCsv;

    public function run(): void
    {
        DB::transaction(function() {
            $this->seedRegions();
            $this->seedDistricts();
            $this->seedCouncils();
            $this->seedChiefdoms();
            $this->seedSectors();
        });
    }

    private function seedRegions(): void
    {
        $rows = $this->csv($this->dataPath('regions.csv'));
        foreach ($rows as $r) {
            $code = $r['code'] ?? null; $name = $r['name'] ?? null;
            if (!$name) continue;
            DB::table('location_regions')->updateOrInsert(
                $code ? ['code'=>$code] : ['name'=>$name],
                ['name'=>$name, 'code'=>$code]
            );
        }
        $this->command?->info('Seeded regions: '.count($rows));
    }

    private function seedDistricts(): void
    {
        $rows = $this->csv($this->dataPath('districts.csv'));
        foreach ($rows as $r) {
            $code = $r['code'] ?? null; $name = $r['name'] ?? null; $regionCode = $r['region_code'] ?? null;
            if (!$name) continue;
            $regionId = null;
            if ($regionCode) {
                $regionId = DB::table('location_regions')->where('code',$regionCode)->value('id');
            }
            if (!$regionId && isset($r['region'])) {
                $regionId = DB::table('location_regions')->where('name',$r['region'])->value('id');
            }
            DB::table('location_districts')->updateOrInsert(
                $code ? ['code'=>$code] : ['name'=>$name],
                ['name'=>$name, 'code'=>$code, 'region_id'=>$regionId]
            );
        }
        $this->command?->info('Seeded districts: '.count($rows));
    }

    private function seedCouncils(): void
    {
        $rows = $this->csv($this->dataPath('councils.csv'));
        foreach ($rows as $r) {
            $name = $r['name'] ?? null; if (!$name) continue;
            $type = $r['type'] ?? 'district';
            $districtId = null; $regionId = null;
            if (isset($r['district_code'])) {
                $districtId = DB::table('location_districts')->where('code',$r['district_code'])->value('id');
            }
            if (!$districtId && isset($r['district'])) {
                $districtId = DB::table('location_districts')->where('name',$r['district'])->value('id');
            }
            if (!$districtId && isset($r['region_code'])) {
                $regionId = DB::table('location_regions')->where('code',$r['region_code'])->value('id');
            }
            DB::table('location_councils')->updateOrInsert(
                ['name'=>$name],
                [
                    'name'=>$name,
                    'type'=>$type,
                    // keep either district_id or region_id if columns exist; ignore if not present
                    'district_id' => $this->columnExists('location_councils','district_id') ? $districtId : null,
                    'region_id'   => $this->columnExists('location_councils','region_id') ? $regionId   : null,
                ]
            );
        }
        $this->command?->info('Seeded councils: '.count($rows));
    }

    private function seedChiefdoms(): void
    {
        $rows = $this->csv($this->dataPath('chiefdoms.csv'));
        foreach ($rows as $r) {
            $name = $r['name'] ?? null; if (!$name) continue;
            $districtId = null;
            if (isset($r['district_code'])) {
                $districtId = DB::table('location_districts')->where('code',$r['district_code'])->value('id');
            }
            if (!$districtId && isset($r['district'])) {
                $districtId = DB::table('location_districts')->where('name',$r['district'])->value('id');
            }
            DB::table('location_chiefdoms')->updateOrInsert(
                ['name'=>$name, 'district_id'=>$districtId],
                ['name'=>$name, 'district_id'=>$districtId]
            );
        }
        $this->command?->info('Seeded chiefdoms: '.count($rows));
    }

    private function seedSectors(): void
    {
        $rows = $this->csv($this->dataPath('sector.csv'));
        foreach ($rows as $r) {
            $code = $r['code'] ?? null; $name = $r['name'] ?? null; if (!$name) continue;
            DB::table('sectors')->updateOrInsert(
                $code ? ['code'=>$code] : ['name'=>$name],
                ['name'=>$name, 'code'=>$code]
            );
        }
        $this->command?->info('Seeded sectors: '.count($rows));
    }

    private function columnExists(string $table, string $column): bool
    {
        static $cache = [];
        $key = $table.'.'.$column;
        if (array_key_exists($key, $cache)) return $cache[$key];
        $exists = collect(DB::select("SHOW COLUMNS FROM `{$table}`"))->pluck('Field')->contains($column);
        return $cache[$key] = $exists;
    }
}
