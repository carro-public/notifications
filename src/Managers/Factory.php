<?php

namespace CarroPublic\Notifications\Managers;

use CarroPublic\Notifications\Senders\Sender;

interface Factory
{
    /**
     * Get a sender instance by name.
     * @param string|null $name
     * @return Sender
     */
    public function sender($channel, $name = null);
}
