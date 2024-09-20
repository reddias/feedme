<?php

namespace App\Http\Controllers;

use App\Events\AddCommentEvent;
use App\Http\Resources\CommentResource;
use App\Jobs\AddCommentJob;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{

    /**
     * Create a new comment for a recipe.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'recipe_id' => 'required|integer|exists:recipes,id',
            'message' => 'required|string',
        ]);
        $request = $request->only(['recipe_id', 'message']);
        $request['user_id'] = auth()->id();

        AddCommentJob::dispatch($request);

        return response()->json(['created' => true]);
    }

    /**
     * Display a paginated listing of comments for a recipe.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'recipe_id' => 'required|integer|exists:recipes,id',
        ]);
        $comments = Comment::query()->where('recipe_id', $request->recipe_id)->paginate();
        return response()->json($comments);
    }

    /**
     * Display the specified comment.
     *
     * @param int $id
     * @return CommentResource
     */
    public function show(int $id): CommentResource
    {
        $comment = Comment::query()->with(['user', 'recipe'])->findOrFail($id);
        return CommentResource::make($comment);
    }

    /**
     * Remove the specified comment created by the authenticated user.
     *
     * @param int $id
     * @return CommentResource
     */
    public function destroy(int $id): CommentResource
    {
        $userId = auth()->id();
        $comment = Comment::query()->where('user_id', $userId)->findOrFail($id);
        $comment->delete();

        return CommentResource::make($comment);
    }
}
