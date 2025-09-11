<?php

namespace App\Observers;

interface Observer {
    public function update($message);
}

class RatingSubject
{
    protected $observers = [];

    public function attach(Observer $observer)
    {
        $this->observers[] = $observer;
    }

    public function detach(Observer $observer)
    {
        $this->observers = array_filter($this->observers, fn($o) => $o !== $observer);
    }

    public function notify($message)
    {
        foreach ($this->observers as $observer) {
            $observer->update($message);
        }
    }
}
