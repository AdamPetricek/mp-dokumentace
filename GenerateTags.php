<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Unit;
use App\Dimension;
use Illuminate\Support\Facades\Schema;

class GenerateTags extends Command
{
  protected $signature = 'generate:tags';

  protected $description = 'Generates printable .txt files with tags. ';

  public function __construct()
  {
      parent::__construct();
  }

  public function handle()
  {
    $dimensions = Dimension::all()->first();

    $max_col_big = ord($dimensions->columns_big) - ord('A') + 1;
    $max_row_big = ord($dimensions->rows_big) - ord('A') + 1;

    $box_units = $dimensions->rows_small * $dimensions->columns_small;
    $box_number = (ord($dimensions->columns_big) - ord('A') + 1) * (ord($dimensions->rows_big) - ord('A') + 1);
    $total_units = ($max_col_big * $dimensions->columns_small) * ($max_row_big * $dimensions->rows_small);

    if (!file_exists('storage/app/soucastky_samolepky')) {
        mkdir('storage/app/soucastky_samolepky', 0777, true);
    }

    for($i = 0; $i < $max_col_big; $i++)
    {
      for($q = 0; $q < $max_row_big; $q++)
      {
        $all = Unit::where("column_big", range('A', 'Z')[$i])->where("row_big", range('A', 'Z')[$q])->orderBy("row_small")->orderBy("column_small")->get();
        $file = fopen("storage/app/soucastky_samolepky/" . range('A', 'Z')[$i] . "-" . range('A', 'Z')[$q] . ".txt", "w");
        fwrite($file, "N\r\nq562\r\nQ80,24\r\nJF\r\nD10\r\nS2\r\nO\r\nI8,2,001");
        $counter = 0;
        $nums = ["A30", "A210", "A400"];
        foreach($all as $key=>$a)
        {
          if($key % 3 == 0 && $key != 0)
          {
            fwrite($file, "\r\nP1");
            $counter = 0;
          }
          fwrite($file, "\r\n". $nums[$counter++] .",10,0,4,1,1,N,\"" . range('A', 'Z')[$i] . "-" . range('A', 'Z')[$q] . " " . $a->column_small . "-" . $a->row_small . "\"");
        }
        fwrite($file, "\r\nP1");
        fwrite($file, "\r\nA30,10,0,4,1,1,N,\" \"");
        fwrite($file, "\r\nP2\r\n");
        fclose($file);
      }
    }
    $this->info("Tags generated!");
  }
}
