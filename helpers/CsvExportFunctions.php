<?php

function csvEscape($str) {
    if (strpbrk($str, "\n,\"") === FALSE) {
        return $str;
    } else {
        return '"' . str_replace('"', '""', $str) . '"';
    } 
}