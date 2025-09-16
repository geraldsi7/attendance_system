<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lecturer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $lecturers = Lecturer::orderBy('name')->get();

        return Inertia::render('Setup/Course', compact('lecturers'));
    }

    public function fetch()
    {

        $data = Course::get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btnClasses = 'flex items-center justify-center p-1 bg-gray-800 text-white font-normal text-sm leading-tight rounded-sm shadow-sm hover:bg-gray-700 hover:shadow-lg focus:bg-gray-700 focus:shadow-md focus:outline-none focus:ring-0 active:bg-gray-800 active:shadow-md transition duration-150 ease-in-out';
                return '
                <div class="flex items-center gap-4">
                <button type="button" data-id="' . $row->id . '" class="assign ' . $btnClasses . '">
                <span class="material-symbols-outlined">
                person_edit
                </span>
                </button>

                <button type="button" data-id="' . $row->id . '" class="edit ' . $btnClasses . '">
                <span class="material-symbols-outlined">
                edit
                </span>
                </button>
               
                <button type="button" data-id="' . $row->id . '" class="delete flex items-center justify-center p-1 w-10 h-10 bg-red-600 text-white font-normal text-sm leading-tight rounded-sm shadow-sm hover:bg-red-700 hover:shadow-lg focus:bg-red-700 focus:shadow-md focus:outline-none focus:ring-0 active:bg-red-800 active:shadow-md transition duration-150 ease-in-out">
                <span class="material-symbols-outlined">
                delete
                </span>
                </button>
                </div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(
            [
                'code' => 'required|string|max:20|unique:courses',
                'title' => 'required|string|max:100|unique:courses',
            ]
        );

        $input = $request->all();

        Course::create($input);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function edit(Request $request)
    {
        $data = Course::find($request->id);

        return response()->json([
            'row'   => $data,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Course $course)
    {
        $request->validate(
            [
                'code' => ['required', 'string', 'max:20', Rule::unique(Course::class)->ignore($course)],
                'title' => ['required', 'string', 'max:100', Rule::unique(Course::class)->ignore($course)],
            ]
        );

        $input = $request->all();

        $course->fill($input)->save();
    }

    public function editAssign(Request $request)
    {

        $data = Course::find($request->id);

        $lecturers = $data->lecturer()
            ->orderBy('name')
            ->get();

        $data['lecturers'] = $lecturers;

        return response()->json([
            'row' => $data
        ]);
    }

    public function storeAssign(Request $request)
    {
        $request->validate(
            [
                'lecturers.*.id'  => ['required', 'distinct'],
            ],
            [
                'lecturers.*.id.required'  => 'The lecturer field is required.',
                'lecturers.*.id.distinct'  => 'The field must have different lecturer.',
            ]
        );

        $input = $request->all();

        $course = Course::find($input['id']);
        $lecturers = [];

        $lecturers_data = $input['lecturers'];

        foreach ($lecturers_data as $lecturer) {
            $lecturers[] = $lecturer['id'];
        }

        $course->lecturer()->sync($lecturers);
    }

    public function destroy(Request $request)
    {
        $data = Course::find($request->id);

        $data->delete();
    }
}
