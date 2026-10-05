@extends('errors.minimal')
@section('code', (string) $exception->getStatusCode())
@section('title', 'Error del servidor')
@section('message', 'No pudimos completar la solicitud. Inténtalo más tarde.')
