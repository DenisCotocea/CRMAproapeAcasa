<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Property;
use App\Models\Comment;

class CommentController extends Controller
{
    /**
     * Store a new comment for a property or lead.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $commentableType
     * @param  int  $commentableId
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $commentableType, $commentableId)
    {
        // Validate the comment input
        $validated = $request->validate([
            'comment' => 'required|string|max:500',
        ]);

        $commentable = $this->getCommentableModel($commentableType, $commentableId);

        if (!$commentable) {
            return back()->with('error', 'Invalid property or lead.');
        }

        // Create a new comment
        $comment = new Comment();
        $comment->comment = $validated['comment'];
        $comment->user_id = auth()->id();
        $comment->commentable()->associate($commentable);
        $comment->save();

        return back()->with('success', 'Your comment has been posted.');
    }

    /**
     * Delete a comment.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comment $comment)
    {
        if ($comment->user_id === auth()->id()) {
            $comment->delete();
            return back()->with('success', 'Comment deleted successfully.');
        }

        return back()->with('error', 'You are not authorized to delete this comment.');
    }

    /**
     * Get the commentable model (Property or Lead).
     *
     * @param  string  $commentableType
     * @param  int  $commentableId
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected function getCommentableModel($commentableType, $commentableId)
    {
        $model = null;

        if ($commentableType === 'App\Models\Property') {
            $model = Property::find($commentableId);
        } elseif ($commentableType === 'App\Models\Lead') {
            $model = Lead::find($commentableId);
        } elseif ($commentableType === 'App\Models\Ticket') {
            $model = Ticket::find($commentableId);
        }

        return $model;
    }
}
