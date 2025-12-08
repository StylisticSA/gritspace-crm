<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TransferGritspaceData extends Command
{
    protected $signature = 'transfer:gritspace-all';
    protected $description = 'Transfer all data from gritspace_3 to officebooking';

    public function handle()
    {
        $this->info('Starting full data transfer...');

        $tables = DB::connection('gritspace')->select('SHOW TABLES');
        $tableKey = array_keys((array) $tables[0])[0];

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            $this->info("Transferring table: $tableName");

            $rows = DB::connection('gritspace')->table($tableName)->get();
            $sourceColumns = DB::connection('gritspace')->getSchemaBuilder()->getColumnListing($tableName);
            $targetColumns = DB::connection('mysql')->getSchemaBuilder()->getColumnListing($tableName);
            $hasId = in_array('id', $sourceColumns) && in_array('id', $targetColumns);

            foreach ($rows as $row) {
                $data = (array) $row;

                // Drop columns not present in target schema
                $filteredData = array_intersect_key($data, array_flip($targetColumns));

                if ($hasId && isset($row->id)) {
                    DB::connection('mysql')->table($tableName)->updateOrInsert(
                        ['id' => $row->id],
                        $filteredData
                    );
                } else {
                    DB::connection('mysql')->table($tableName)->insert($filteredData);
                }
            }
        }

        $this->info('✅ All data transferred successfully.');
    }
}
