@extends('errors.minimal')
@section('code', (string) $exception->getStatusCode())
@section('title', 'No se pudo completar la solicitud')
@section('message', 'Revisa la dirección o los datos enviados e inténtalo de nuevo.')
