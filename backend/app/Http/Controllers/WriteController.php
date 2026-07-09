<?php

namespace App\Http\Controllers;

use App\Services\RfidBridgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WriteController extends Controller
{
    public function __construct(private RfidBridgeService $bridge) {}

    public function writeEpc(Request $request): JsonResponse
    {
        $data = $request->validate([
            'antenna'  => 'required|integer|min:1',
            'data'     => 'required|string',
            'start'    => 'sometimes|integer',
        ]);
        return response()->json($this->bridge->writeEpc($data));
    }

    public function writeEpcFilter(Request $request): JsonResponse
    {
        $data = $request->validate([
            'antenna'    => 'required|integer|min:1',
            'data'       => 'required|string',
            'start'      => 'sometimes|integer',
            'filter_tid' => 'required|string',
        ]);
        return response()->json($this->bridge->writeEpcFilter($data));
    }

    public function writeEpcUserData(Request $request): JsonResponse
    {
        $data = $request->validate([
            'antenna' => 'required|integer|min:1',
            'data'    => 'required|string',
            'start'   => 'sometimes|integer',
        ]);
        return response()->json($this->bridge->writeEpcUserData($data));
    }

    public function writeEpcReserved(Request $request): JsonResponse
    {
        $data = $request->validate([
            'antenna' => 'required|integer|min:1',
            'data'    => 'required|string',
            'start'   => 'sometimes|integer',
        ]);
        return response()->json($this->bridge->writeEpcReserved($data));
    }

    public function write6bUserData(Request $request): JsonResponse
    {
        $data = $request->validate([
            'antenna'    => 'required|integer|min:1',
            'match_tid'  => 'required|string',
            'data'       => 'required|string',
            'start'      => 'sometimes|integer',
        ]);
        return response()->json($this->bridge->write6bUserData($data));
    }

    public function writeGbEpc(Request $request): JsonResponse
    {
        $data = $request->validate([
            'antenna' => 'required|integer|min:1',
            'data'    => 'required|string',
            'area'    => 'sometimes|integer',
            'start'   => 'sometimes|integer',
        ]);
        return response()->json($this->bridge->writeGbEpc($data));
    }

    public function writeGbEpcFilter(Request $request): JsonResponse
    {
        $data = $request->validate([
            'antenna'    => 'required|integer|min:1',
            'data'       => 'required|string',
            'area'       => 'sometimes|integer',
            'start'      => 'sometimes|integer',
            'filter_tid' => 'required|string',
        ]);
        return response()->json($this->bridge->writeGbEpcFilter($data));
    }

    public function writeGbUserData(Request $request): JsonResponse
    {
        $data = $request->validate([
            'antenna' => 'required|integer|min:1',
            'data'    => 'required|string',
            'area'    => 'sometimes|integer',
            'start'   => 'sometimes|integer',
        ]);
        return response()->json($this->bridge->writeGbUserData($data));
    }

    public function writeGbSafe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'antenna' => 'required|integer|min:1',
            'data'    => 'required|string',
            'start'   => 'sometimes|integer',
        ]);
        return response()->json($this->bridge->writeGbSafe($data));
    }
}
