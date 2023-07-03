<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    public function index()
    {
       
        $events = Event::all();

        return view('welcome', [
            'events' => $events
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
        $event->city = $request->city;
        $event->private = $request->private;
        $event->description = $request->description;

        $event->save();

        return redirect('/')->with('msg', 'Evento Criado com sucesso!');
    }
    
}
