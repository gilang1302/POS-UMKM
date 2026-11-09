<?php

namespace App\Exceptions;

use Exception;

// error yang pesannya aman ditampilkan langsung ke pengguna
// (misalnya stok tidak cukup, uang bayar kurang)
class StokException extends Exception
{
    //
}
