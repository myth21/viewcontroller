<?php


namespace myth21\viewcontroller;


interface ViewInterface
{
    public function render(string $name, array $data = []): string;
}