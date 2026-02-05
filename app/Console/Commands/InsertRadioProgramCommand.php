<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Routine\InsertRadioProgram;

class InsertRadioProgramCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'radio:insert-programs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '全放送局の週間番組データをDBに一括登録します';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('番組データの登録を開始します...');

        try {
            $insertProgram = new InsertRadioProgram();
            $insertProgram();

            $this->info('番組データの登録が完了しました！');
            return 0;
        } catch (\Exception $e) {
            $this->error('エラーが発生しました: ' . $e->getMessage());
            return 1;
        }
    }
}
