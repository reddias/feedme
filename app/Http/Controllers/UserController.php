<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends Controller
{
    private FileUploadService $fileUploadService;

    public function __construct()
    {
        $this->fileUploadService = new FileUploadService();
    }

    /**
     * Store a newly created user in the database.
     *
     * Handles file upload through FileUploadService and stores user information.
     *
     * @param Request $request
     * @return UserResource
     */
    public function create(Request $request): UserResource
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $user = User::query()->create($request->all());
        $user->assignRole('user');

        if ($request['photo']) {
            $fileData = $this->fileUploadService->processFile($request->file('photo'), "photos/users/$user->id");
            $user->photo_url = $fileData['fileUrl'];
            $user->save();
        }
        return UserResource::make($user);
    }

    /**
     * Retrieve the currently authenticated user's details.
     *
     * @return UserResource
     */
    public function me(): UserResource
    {
        return UserResource::make(auth()->user()->load(['likes', 'recipes']));
    }

    /**
     * Retrieve a paginated list of users.
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $users = User::query()->paginate();
        return UserResource::collection($users);
    }

    /**
     * Retrieve a specific user by ID.
     *
     * @param int $id
     * @return UserResource
     */
    public function show(int $id): UserResource
    {
        $user = User::query()->with(['likes', 'recipes'])->findOrFail($id);
        return UserResource::make($user);
    }

    /**
     * Update the currently authenticated user's details.
     *
     * @param Request $request
     * @return UserResource
     */
    public function updateMe(Request $request): UserResource
    {
        $userId = auth()->id();
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
        ]);
        $user = User::query()->findOrFail($userId);
        $user->update($request->all());
        return UserResource::make($user);
    }

    /**
     * Soft delete the currently authenticated user by updating the status to 'deleted'.
     *
     * @return UserResource
     */
    public function destroyMe(): UserResource
    {
        $userId = auth()->id();
        $user = User::query()->findOrFail($userId);
        $user->update(['status' => 'deleted']);
        auth()->logout();
        return UserResource::make($user);
    }

    /**
     * Update the status of a specific user.
     *
     * This method only allows updating the status of users who are not admins.
     *
     * @param Request $request
     * @param int $id
     * @return UserResource
     */
    public function updateStatus(Request $request, int $id): UserResource
    {
        $request->validate([
            'status' => 'required|in:active,blocked,deleted',
        ]);

        $user = User::query()->where('is_admin', 0)->findOrFail($id);
        $user->status = $request->status;
        $user->save();
        return UserResource::make($user);
    }

    /**
     * Update the photo of the currently authenticated user.
     *
     * @param Request $request
     * @return UserResource
     */
    public function updatePhoto(Request $request): UserResource
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $user = auth()->user();
        $user = $this->fileUploadService->updateImage($user, $request->file('photo'), "photos/users/$user->id");
        return UserResource::make($user);
    }

    /**
     * Delete the photo of the currently authenticated user.
     *
     * @return UserResource
     */
    public function deletePhoto(): UserResource
    {
        $user = auth()->user();
        $user = $this->fileUploadService->deleteImage($user);
        return UserResource::make($user);
    }

    /**
     * Change the password
     * For Everyone
     *
     * @param Request $request
     * @return JsonResponse|UserResource
     */
    public function changePassword(Request $request): JsonResponse|UserResource
    {
        $userId = auth()->id();

        $user = User::query()->findOrFail($userId);
        $previousPassword = $user->password;

        $request->validate([
            'previous_password' => 'required|string',
        ]);

        if (!Hash::check($request->previous_password, $previousPassword)) {
            return response()->json([
                'message' => 'The previous password is incorrect.',
                'errors' => [
                    'password' => [
                        'The previous password is incorrect.',
                    ]
                ]
            ], 422);
        }

        $request->validate([
            'password' => 'required|string|min:6',
        ]);

        $user->update([
            'password' => $request->password,
        ]);

        return UserResource::make($user);
    }
}
