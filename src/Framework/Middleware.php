<?php
    namespace App\Framework;

    interface Middleware
    {
        public function handle(): bool;
    }