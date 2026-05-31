<?php

use Livewire\Component;

new class () extends Component {
    //
};
?>

<div>
    @auth
        <p>{{ auth()->user()->name  }}</p>
    @endauth
</div>