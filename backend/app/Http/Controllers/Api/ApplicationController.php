<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationRequest;
use App\Http\Resources\ApplicationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ApplicationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return ApplicationResource::collection($request->user()->applications()->latest('id')->get());
    }

    public function store(StoreApplicationRequest $request): ApplicationResource
    {
        return new ApplicationResource($request->user()->applications()->create($request->validated()));
    }

    public function show(Request $request, int $application): ApplicationResource
    {
        return new ApplicationResource($request->user()->applications()->findOrFail($application));
    }

    public function update(StoreApplicationRequest $request, int $application): ApplicationResource
    {
        $record = $request->user()->applications()->findOrFail($application);
        $record->update($request->validated());

        return new ApplicationResource($record->refresh());
    }

    public function destroy(Request $request, int $application): Response
    {
        $request->user()->applications()->findOrFail($application)->delete();

        return response()->noContent();
    }
}
