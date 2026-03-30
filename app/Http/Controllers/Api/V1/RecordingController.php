<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RecordingController extends Controller
{
    /**
     * Ingest a completed recording from Jibri.
     */
    public function ingest(Request $request)
    {
        $expectedToken = (string) config('services.jitsi.recording_ingest_secret', '');
        $providedToken = (string) ($request->bearerToken() ?: $request->header('X-Jitsi-Recording-Secret') ?: '');

        if ($expectedToken === '' || !hash_equals($expectedToken, $providedToken)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'meeting_name' => 'required|string',
            'file_path' => 'required|string',
            'file_name' => 'required|string',
            'status' => 'required|string'
        ]);

        Log::info('Jibri recording ingested: ', $validated);

        // Here you can update the Meeting record in the database with the recording path
        // e.g., Meeting::where('room_name', $validated['meeting_name'])->update(['recording_url' => '/config/recordings/' . $validated['file_name']]);

        return response()->json([
            'message' => 'Recording successfully ingested',
            'data' => $validated
        ]);
    }
}
