<?php

namespace App\Http\Controllers;

use App\Services\PostInteractionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostInteractionController extends Controller
{
    protected $interactionService;

    public function __construct(PostInteractionService $interactionService)
    {
        $this->middleware('auth');
        $this->interactionService = $interactionService;
    }

    /**
     * 投稿にいいねする
     */
    public function like(Request $request)
    {
        $request->validate([
            'post_id' => 'required|integer|exists:posts,id',
        ]);

        $result = $this->interactionService->likePost(
            $request->post_id,
            Auth::id()
        );

        return response()->json($result);
    }

    /**
     * いいねを取り消す
     */
    public function unlike(Request $request)
    {
        $request->validate([
            'post_id' => 'required|integer|exists:posts,id',
        ]);

        $result = $this->interactionService->unlikePost(
            $request->post_id,
            Auth::id()
        );

        return response()->json($result);
    }

    /**
     * コメントを投稿
     */
    public function comment(Request $request)
    {
        $request->validate([
            'post_id' => 'required|integer|exists:posts,id',
            'body' => 'required|string|max:1000',
        ], [
            'body.required' => 'コメントを入力してください',
            'body.max' => 'コメントは1000文字以内で入力してください',
        ]);

        $result = $this->interactionService->addComment(
            $request->post_id,
            Auth::id(),
            $request->body
        );

        return response()->json($result);
    }

    /**
     * コメントを削除
     */
    public function deleteComment(Request $request)
    {
        $request->validate([
            'comment_id' => 'required|integer|exists:post_comments,id',
        ]);

        $result = $this->interactionService->deleteComment(
            $request->comment_id,
            Auth::id()
        );

        return response()->json($result);
    }

    /**
     * コメント一覧を取得
     */
    public function getComments(Request $request)
    {
        $request->validate([
            'post_id' => 'required|integer|exists:posts,id',
        ]);

        $comments = $this->interactionService->getComments($request->post_id);

        return response()->json([
            'success' => true,
            'data' => [
                'comments' => $comments->items(),
                'total' => $comments->total(),
                'per_page' => $comments->perPage(),
                'current_page' => $comments->currentPage(),
            ],
        ]);
    }

    /**
     * いいね状態を確認
     */
    public function checkLike(Request $request)
    {
        $request->validate([
            'post_id' => 'required|integer|exists:posts,id',
        ]);

        $hasLiked = $this->interactionService->hasLiked(
            $request->post_id,
            Auth::id()
        );

        return response()->json([
            'success' => true,
            'data' => ['liked' => $hasLiked],
        ]);
    }
}
