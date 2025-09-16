<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Classe;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Lecturer;
use App\Models\Section;
use App\Models\Level;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;

class ClasseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $lecturers = Lecturer::orderBy('name')->get();
        $departments = Department::orderBy('title')->get();
        $levels = Level::orderBy('title')->get();
        $sections = Section::orderBy('title')->get();
        $courses = Course::orderBy('code')->get();

        return Inertia::render('Setup/Classe', compact(
            'lecturers',
            'departments',
            'levels',
            'sections',
            'courses'
        ));
    }

    public function fetch()
    {

        $data = Classe::get();

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

                <div class="flex items-center gap-4">
                <button type="button" data-id="' . $row->id . '" class="assign-course ' . $btnClasses . '">
                <span class="material-symbols-outlined">
                checkbook
                </span>
                </button>
                
                <div class="flex items-center gap-4">
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
            ->editColumn('department', function ($row) {
                return $row->department->title;
            })
            ->editColumn('level', function ($row) {
                return $row->level->title;
            })
            ->editColumn('section', function ($row) {
                $sectionCount = Classe::where('department_id', $row->department->id)
                    ->where('level_id', $row->level->id)
                    ->distinct('section_id')
                    ->count('section_id');

                return $sectionCount > 1 ? $row->section->title : '- -';
            })
            ->rawColumns(['department', 'level', 'section', 'action'])
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
                'department_id' => 'required',
                'level_id' => 'required',
                'section_id' => [
                    'required',
                    Rule::unique('classes')->where(function ($query) use ($request) {
                        // Apply the unique constraint for the specific grade_id
                        return $query->where('department_id', $request->department_id)
                            ->where('level_id', $request->level_id);
                    }),
                ],
            ],
            [
                'department_id.required' => 'The department field is required.',
                'level_id.required' => 'The level field is required.',
                'section_id.required' => 'The section field is required.',
                'section_id.unique' => 'The combination of department, level and section already exists.'
            ]
        );

        $input = $request->all();


        Classe::create($input);
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
        $data = Classe::find($request->id);

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
    public function update(Request $request, Classe $classe)
    {
        $request->validate(
            [
                'department_id' => 'required',
                'level_id' => 'required',
                'section_id' => [
                    'required',
                    Rule::unique('classes')->where(function ($query) use ($request) {
                        // Apply the unique constraint for the specific grade_id
                        return $query->where('department_id', $request->department_id)
                            ->where('level_id', $request->level_id);
                    })->ignore($classe),
                ],
            ],
            [
                'department_id.required' => 'The department field is required.',
                'level_id.required' => 'The level field is required.',
                'section_id.required' => 'The section field is required.',
                'section_id.unique' => 'The combination of department, level and section already exists.'
            ]
        );

        $input = $request->all();

        $classe->fill($input)->save();
    }

    public function editAssign(Request $request)
    {

        $data = Classe::find($request->id);

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

        $classe = Classe::find($input['id']);
        $lecturers = [];

        $lecturers_data = $input['lecturers'];

        foreach ($lecturers_data as $lecturer) {
            $lecturers[] = $lecturer['id'];
        }

        $classe->lecturer()->sync($lecturers);
    }

    public function editAssignCourse(Request $request)
    {

        $data = Classe::find($request->id);

        $courses = $data->course()
            ->orderBy('code')
            ->get();

        $data['courses'] = $courses;

        return response()->json([
            'row' => $data
        ]);
    }

    public function storeAssignCourse(Request $request)
    {
        $request->validate(
            [
                'courses.*.id'  => ['required', 'distinct'],
            ],
            [
                'courses.*.id.required'  => 'The course field is required.',
                'courses.*.id.distinct'  => 'The field must have different course.',
            ]
        );

        $input = $request->all();

        $classe = Classe::find($input['id']);
        $courses = [];

        $courses_data = $input['courses'];

        foreach ($courses_data as $course) {
            $courses[] = $course['id'];
        }

        $classe->course()->sync($courses);
    }

    public function destroy(Request $request)
    {
        $data = Classe::find($request->id);

        $data->lecturer()->detach();

        $students = $data->student;

        foreach ($students as $student) {
            $student->classe()->dissociate();
            $student->save();
        }

        $data->delete();
    }
}
