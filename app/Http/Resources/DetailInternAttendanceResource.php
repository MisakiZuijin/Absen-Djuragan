<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailInternAttendanceResource extends JsonResource {
    protected $meta;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @param  mixed  $meta
     * @return void
     */
    public function __construct($resource, $meta = null) {
        parent::__construct($resource);
        $this->meta = $meta;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            "status" => true,
            "status_code" => 200,
            "message" => "success retrieve data",
            "data" => $this->resource["data"],
            "meta" => $this->resource["pagination"]
        ];
    }
}
