<?php

class ExpeditionsTableSeeder extends Seeder
{
    private $expeditions;

    public function run()
    {
        Eloquent::unguard();

        $this->expeditions = $this->loadData();

        foreach ($this->expeditions as $expedition) {
            Expedition::create($expedition);
        }
    }

    public function loadData()
    {
        require_once 'data/expeditions.php';

        return $items;
    }
}
