<?php
namespace Controller;
use Model\Subdivision;
use Src\Request;
use Src\View;
class Api
{
    public function index(): void
    {
        $subdivisions = Subdivision::all()->toArray();
        (new View())->toJSON($subdivisions);
    }

    public function echo(Request $request): void
    {
        (new View())->toJSON($request->all());
    }
}