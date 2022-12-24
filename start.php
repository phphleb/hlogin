<?php
    /**
     * @author  Foma Tuturov <fomiash@yandex.ru>
     */

    if (end($argv) === '--help') {
        die (
            "\n" . "HLOGIN: Registration module for the HLEB project." .
            "\n" . "--remove (delete module)" .
            "\n" . "--add    (add/update module)" . "\n"
        );
    }

    if (end($argv) === '--remove') {
        include __DIR__ . "/remove_hlogin.php";
    } else if (end($argv) === '--add') {
        include __DIR__ . "/add_hlogin.php";
    } else {
        exit(PHP_EOL . 'For details, repeat the command with the `--help` flag.' . PHP_EOL);
    }

