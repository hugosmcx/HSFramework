<?php
    namespace HSFramework;

    interface Middleware
    {
        public function handle(): bool;
    }