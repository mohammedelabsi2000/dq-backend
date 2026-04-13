<?php

namespace App\Contracts;

interface HasLabelAndCode
{
    public function code(): string;
    public function labels(): array;
    public function label(): string;
}