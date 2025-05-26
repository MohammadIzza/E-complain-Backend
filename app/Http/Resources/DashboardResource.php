<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_complains'      => $this['total_complains'],
            'active_complains'     => $this['active_complains'],
            'resolved_complains'   => $this['resolved_complains'],
            'avg_resolution_time'  => $this['avg_resolution_time'],
            'status_distribution'  => [
                'open'         => $this['status_distribution']['open'],
                'onprogres'    => $this['status_distribution']['onprogres'],
                'resolved'     => $this['status_distribution']['resolved'],
                'rejected'     => $this['status_distribution']['rejected'],
            ],
        ];
    }
}