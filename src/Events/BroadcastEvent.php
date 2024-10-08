<?php

namespace GemaDigital\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

abstract class BroadcastEvent extends DefaultEvent implements ShouldBroadcast {}
