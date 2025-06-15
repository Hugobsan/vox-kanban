<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLabelRequest;
use App\Http\Requests\UpdateLabelRequest;
use App\Models\Label;
use App\Models\Board;

class LabelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Board $board)
    {
        $this->authorize('view', $board);

        try {
            $labels = $board->labels()->orderBy('name')->get();

            return $this->respond()->successResponse($labels, 'Labels retrieved successfully!');
        } catch (\Exception $e) {
            return $this->respond()->errorResponse('Error retrieving labels: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLabelRequest $request)
    {
        $board = Board::findOrFail($request->board_id);
        $this->authorize('update', $board);

        try {
            $label = $board->labels()->create($request->validated());

            return $this->respond()->successResponse($label, 'Label created successfully!', 201);
        } catch (\Exception $e) {
            return $this->respond()->errorResponse('Error creating label: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Label $label)
    {
        return $this->respond()->view('label.show', [
            'label' => $label,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Label $label)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLabelRequest $request, Label $label)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Label $label)
    {
        //
    }
}
