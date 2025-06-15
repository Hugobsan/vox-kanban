<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SmartResponse
{
    protected string $view;
    protected array $data = [];
    protected int $status = 200;

    /**
     * Renderiza a view com os dados fornecidos ou retorna uma resposta JSON se solicitado.
     * @param string $view
     * @param array $data
     * @param mixed $request
     * @param int $status
     * @return JsonResponse|Response|SmartResponse
     */
    public function view(string $view, array $data = [], ?Request $request = null, int $status = 200): JsonResponse|Response|SmartResponse
    {
        $this->view = $view;
        $this->data = $data;
        $this->status = $status;

        return $request
            ? $this->asJsonIfRequested($request)
            : $this;
    }

    public function withView(string $view): self
    {
        $this->view = $view;
        return $this;
    }

    public function withData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function withStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function asJsonIfRequested(Request $request): JsonResponse|Response
    {
        if ($request->wantsJson()) {
            return response()->json($this->data, $this->status);
        }

        return response()->view($this->view, $this->data, $this->status);
    }

    /**
     * Retorna uma resposta de sucesso.
     */
    public static function success($data = null, string $message = 'Operação realizada com sucesso', int $status = 200): JsonResponse
    {
        return (new self())->successResponse($data, $message, $status);
    }

    /**
     * Retorna uma resposta de erro.
     */
    public static function error(string $message = 'Erro interno do servidor', int $status = 500, $errors = null): JsonResponse
    {
        return (new self())->errorResponse($message, $status, $errors);
    }

    /**
     * Retorna uma resposta de sucesso (método de instância).
     */
    public function successResponse($data = null, string $message = 'Operação realizada com sucesso', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Retorna uma resposta de erro (método de instância).
     */
    public function errorResponse(string $message = 'Erro interno do servidor', int $status = 500, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }
}
