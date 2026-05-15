<?php

namespace App\Http\Controllers;

use App\Models\DesignSession;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DesignLeadController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'environment_id' => ['required', 'exists:environments,id'],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
            'project_type' => ['nullable', 'string', 'max:100'],
            'preferred_contact_method' => ['nullable', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:2000'],
            'snapshot' => ['nullable', 'array'],
            'final_image' => ['nullable', 'string'],
        ]);

        $session = DesignSession::create([
            'environment_id' => $data['environment_id'],
            'public_uuid' => (string) Str::uuid(),
            'visitor_name' => $data['name'],
            'visitor_email' => $data['email'] ?? null,
            'visitor_phone' => $data['phone'] ?? null,
            'status' => 'lead_created',
            'snapshot' => $data['snapshot'] ?? null,
            'final_image' => $data['final_image'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        Lead::create([
            'design_session_id' => $session->id,
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'city' => $data['city'] ?? null,
            'project_type' => $data['project_type'] ?? null,
            'preferred_contact_method' => $data['preferred_contact_method'] ?? 'whatsapp',
            'message' => $data['message'] ?? null,
            'status' => 'new',
        ]);

        return back()->with('success', 'Tu solicitud fue enviada correctamente.');
    }
}
