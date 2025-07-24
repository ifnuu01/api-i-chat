<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::where('role', '!=', 'admin')->orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::where('id', $id)->where('role', '!=', 'admin')->first();

        if (!$user) {
            return response()->json([
                'success' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2'
        ]);

        $currentUserId = $request->user()->id;
        $query = $request->input('query');

        $users = User::where('id', '!=', $currentUserId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'email')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function block(string $id)
    {
        $user = User::where('id', $id)->where('role', '!=', 'admin')->first();
        if (!$user) {
            return response()->json([
                'success' => false,
            ]);
        }
        $user->update([
            'is_blocked' => true,
        ]);
        return response()->json([
            'success' => true,
            'message' => 'User berhasil diblokir'
        ]);
    }

    public function unblock(string $id)
    {
        $user = User::where('id', $id)->where('role', '!=', 'admin')->first();
        if (!$user) {
            return response()->json([
                'success' => false,
            ]);
        }
        $user->update(['is_blocked' => false]);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil di-unblock'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::where('id', $id)->where('role', '!=', 'admin')->first();
        if (!$user) {
            return response()->json([
                'success' => false,
            ]);
        }
        $user->delete();
        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus'
        ]);
    }
}
