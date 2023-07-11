<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;

class EventController extends Controller
{
    public function index()
    {
       
        $search = request('search');

        if($search) {
            $events = Event::where([
                ['title', 'like', '%'.$search.'%']
            ])->get();
        } else {
            $events = Event::all();
        }

       

        return view('welcome', [
            'events' => $events,
            'search' => $search,
        ]);
    }

    public function create()
    {
        return view('events.create');
    }

    public function products()
    {

        $busca = request('search');

        return view('products', ['busca' => $busca]);
    }

    public function products_teste($id = null)
    {
        return view('product', ['id' => $id]);
    }

    public function contact() 
    {
        return view('contact');
    }

    public function store(Request $request)
    {
        $event = new Event;
        $event->title = $request->title;
        $event->date = $request->date;
        $event->city = $request->city;
        $event->private = $request->private;
        $event->description = $request->description;
        $event->items = $request->items;

        // Image Upload

        if($request->hasFile('image') && $request->file('image')->isValid()) {

            $requestImage = $request->image;

            $extension = $requestImage->extension();

            $imageName = md5($requestImage->getClientOriginalName() . strtotime("now")) . "." . $extension;

            $requestImage->move(public_path('img/events'), $imageName);

            $event->image = $imageName;

        }

        $user = auth()->user();
        $event->user_id = $user->id;

        $event->save();

        return redirect('/')->with('msg', 'Evento Criado com sucesso!');
    }
    

    public function show($id) 
    {
        $event = Event::findOrFail($id);
        $eventOwner = User::where('id', $event->user_id)->first()->toArray();

        return view('events.show', [
            'event' => $event,
            'eventOwner' => $eventOwner 
        ]);
    }

    public function dashboard()
    {
        $user = auth()->user();

        $events = $user->events;

        $eventsAsParticipant = $user->eventsAsParticipant;

        return view('events.dashboard',[
            'events' => $events,
            'eventsasparticipant' => $eventsAsParticipant
        ]);
    }

    public function destroy($id)
    {
        Event::findOrFail($id)->delete();

        return redirect('/dashboard')->with('msg','Evento excluido com sucesso!');
    }

    public function edit($id) 
    {

        $user = auth()->user();
        $event = Event::findOrFail($id);

        if($user->id != $event->user_id) {
            return redirect('/dashboard');
        }

        return view('events.edit', [
            'event' => $event
        ]);
    }

    public function update(Request $request)
    {
        $event = Event::findOrFail($request->id);
        $data = $request->all();

        if ($request->hasFile('image') && $request->file('image')->isValid()) {

            unlink(public_path('img/events/' . $event->image));
            $requestImage = $request->image;
            $extension = $requestImage->extension();
            $imageName = md5($requestImage->getClientOriginalName() . strtotime("now")) . "." . $extension;
            $requestImage->move(public_path('img/events'), $imageName);
            $data['image'] = $imageName;
        }
        Event::findOrFail($request->id)->update($data);

        return redirect('/dashboard')->with('msg', 'Evento editado com sucesso!');
    }

    public function joinEvent($id) {
        $user = auth()->user();
        $event = Event::findOrFail($id);
    
        // Verificar se o usuário já está na lista de participantes
        if ($event->users->contains($user->id)) {
            return back()->with('msg', 'Você já confirmou presença neste evento.');
        }
    
        $user->eventsAsParticipant()->attach($id);
    
        return back()->with('msg', 'Sua presença está confirmada no evento ' . $event->title);
    }
}
