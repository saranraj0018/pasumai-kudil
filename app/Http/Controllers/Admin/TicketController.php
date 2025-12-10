<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Ticket;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Events\NewNotification;
use App\Services\FirebaseService;
use App\Http\Controllers\Controller;

class TicketController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function index(Request $request)
    {
        $this->data['ticket_list'] = Ticket::with('get_user')
            ->orderBy('id', 'asc')
            ->paginate(10);

         return view('admin.ticket_lists')->with($this->data);
    }

    public function saveTicket(Request $request)
    {
        $rules = [
            'status'  => 'required',
         ];
        $request->validate($rules);
        try {
            $update = Ticket::where('id', $request->ticket_id)->update([
                'status' => $request->status,
            ]);
            $ticket = Ticket::where('id', $request->ticket_id)->first();
            $user = User::where('id', $ticket->user_id)->first();
            if($request->status == 2){
               $status = 'Rejected';
            }else if($request->status == 3){
                $status = 'Closed';
            }

            if ($user->fcm_token) {
                $notification = new Notification();
                $notification->user_id = $user->id;
                $notification->title = 'Ticket Status Changed';
                $notification->description = "Your ticket has been $status sucessfully!";
                $notification->type = 1;
                $notification->role = 2;
                $notification->save();

                $this->firebase->sendNotification(
                    $user->fcm_token,
                   'Ticket Status Changed',
                    "Your ticket has been $status sucessfully!",
                );
            }
            return response()->json([
                'success' => true,
                'message' => 'Ticket status updated successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save ticket status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
