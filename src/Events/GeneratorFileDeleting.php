<?php

namespace InfyOm\Generator\Events;

use Illuminate\Queue\SerializesModels;

/**
 * Class GeneratorFileDeleting.
 */
class GeneratorFileDeleting
{
    use SerializesModels;

    /**
     * Type of event
     *
     * @var string
     */
    public $type;

    /**
     * Event payload
     *
     * @var array
     */
    public $data;

    /**
     * Create a new event instance.
     *
     * @param string $type
     * @param array  $data
     */
    public function __construct($type, $data)
    {
        $this->type = $type;
        $this->data = $data;
    }
}
