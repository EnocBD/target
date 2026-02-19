<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;
use Illuminate\Http\Request;

class FormSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $query = FormSubmission::query();

        // Filter by form type
        if ($request->has('type') && $request->type) {
            $query->where('form_type', $request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Search in form data
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereJsonContains('form_data', [['value' => $search]])
                  ->orWhereRaw('JSON_EXTRACT(form_data, "$[*].value") LIKE ?', ["%{$search}%"]);
            });
        }

        $submissions = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.forms.index', compact('submissions'));
    }

    public function show(FormSubmission $submission)
    {
        return view('admin.forms.show', compact('submission'));
    }

    public function markAsRead(FormSubmission $submission)
    {
        $submission->markAsRead();
        
        return redirect()->back()->with('success', 'Formulario marcado como leído');
    }

    public function archive(FormSubmission $submission)
    {
        $submission->archive();
        
        return redirect()->back()->with('success', 'Formulario archivado');
    }

    public function updateNotes(Request $request, FormSubmission $submission)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        $submission->update([
            'notes' => $request->notes
        ]);

        return redirect()->back()->with('success', 'Notas actualizadas');
    }

    public function destroy(FormSubmission $submission)
    {
        $submission->delete();
        
        return redirect()->route('admin.forms.index')->with('success', 'Formulario eliminado');
    }
}
