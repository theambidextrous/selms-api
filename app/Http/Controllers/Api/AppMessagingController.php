<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\ApproveMessageRequest;
use App\Http\Requests\PageableRequest;
use App\Models\AppMessage;

class AppMessagingController extends Controller
{
    /**
     * @OA\Post(
     *     path="/pci/api/v1/messages/approve",
     *     tags={"Messages"},
     *     summary="Add school administrator",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function approveAndSend(ApproveMessageRequest $idList)
    {
        if( !Auth::user()->is_super )
        {
            return response([
                'status' => 400,
                'message' => 'Permission Denied. Only super admins allowed.',
                'errors' => [],
            ], 400);
        }
        try{
            
            $messages = AppMessage::whereIn('id', $idList)->get();
            foreach($messages as $message):
                $this->sendMessage($message);
            endforeach;
            $messages->update(['send' => 1, 'approved' => 1]);

            return response([
                'status' => 200,
                'message' => 'Message approved and send',
                'data' => [],
            ], 200);

        } catch (\Illuminate\Database\QueryException $e) {
            return response([
                'status' => 400,
                'message' => "Server error. Invalid data",
                'errors' => $e->getMessage(),
            ], 400);
        } catch (PDOException $e) {
            return response([
                'status' => 400,
                'message' => "Db error. Invalid data",
                'errors' => $e->getMessage(),
            ], 400);
        }
    }

     /**
     * @OA\Get(
     *     path="/pci/api/v1/messages/findall",
     *     tags={"Messages"},
     *     summary="List students",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function findall(PageableRequest $request) {
         if( !Auth::user()->is_super )
        {
            return response([
                'status' => 400,
                'message' => 'Permission Denied. Only super admins allowed.',
                'errors' => [],
            ], 400);
        }
        $collection = AppMessage::query()
            ->where('approved', 0)
            ->orderBy('created_at', 'desc');

        $pageable = $request->defaults();
        $data = $collection->paginate( $pageable['size'], ['*'], 'page', $pageable['page']);
        return response([
            'status' => 200,
            'message' => "Done successfully",
            'data' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'last_page' => $data->lastPage(),
            ]
        ], 200);
    }

    protected function sendMessage($appMessage) {
        //ToDO send API
        return true;
    }

}
