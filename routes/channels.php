<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('launchCampaign.{user_id}', function () {
	echo "fkkfokofk";
	print_r("expression");
    return $user->id === Order::findOrNew($orderId)->user_id;
});