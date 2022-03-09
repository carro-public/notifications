<?php

namespace CarroPublic\Notifications\Senders;

interface Factory
{
    /**
     * Get a sender instance by name.
     * @param string|null $name
     * @return Sender
     */
    public function sender($channel, $name = null);
}
