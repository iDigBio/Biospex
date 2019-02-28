<?php

namespace App\Console\Commands;

use App\Models\Group;
use Illuminate\Console\Command;

class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // 7f265c7946f02bf7

        // lybb6u6q2y1y
        // harley
        // 04051409
        // harley4196

        // vfbsnjxh6sm3
        dd($this->mySqlOldPassword('harley4196'));

    }

    /**
     * Equal to OLD_PASSWORD() from MySql before 5.7
     * Added to help with legacy passwords after DB upgrade.
     */
    function mySqlOldPassword($input, $hex = true) {
        $nr    = 1345345333;
        $add   = 7;
        $nr2   = 0x12345671;
        $tmp   = null;
        $inlen = strlen($input);
        for ($i = 0; $i < $inlen; $i++) {
            $byte = substr($input, $i, 1);
            if ($byte == ' ' || $byte == "\t") {
                continue;
            }
            $tmp = ord($byte);
            $nr ^= ((($nr & 63) + $add) * $tmp) + (($nr << 8) & 0xFFFFFFFF);
            $nr2 += (($nr2 << 8) & 0xFFFFFFFF) ^ $nr;
            $add += $tmp;
        }
        $out_a  = $nr & ((1 << 31) - 1);
        $out_b  = $nr2 & ((1 << 31) - 1);
        $output = sprintf("%08x%08x", $out_a, $out_b);
        if ($hex) {
            return $output;
        }

        return $this->hexHashToBin($output);
    }

    /**
     * @see mySqlOldPassword above
     * @param $hex
     * @return string
     */
    function hexHashToBin($hex) {
        $bin = "";
        $len = strlen($hex);
        for ($i = 0; $i < $len; $i += 2) {
            $byte_hex  = substr($hex, $i, 2);
            $byte_dec  = hexdec($byte_hex);
            $byte_char = chr($byte_dec);
            $bin .= $byte_char;
        }

        return $bin;
    }
}
