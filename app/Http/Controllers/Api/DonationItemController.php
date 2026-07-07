<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\DonationImagesRepositoryInterface;
use App\Contracts\Services\DonationItemServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreDonationItemRequest;
use App\Http\Requests\Api\UpdateDonationItemRequest;
use App\Http\Requests\Api\UploadDonationImageRequest;
use App\Models\DonationItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Donation Items",
 *     description="Endpoints para gerenciamento de itens de doação"
 * )
 */
class DonationItemController extends Controller
{
    public function __construct(
        private DonationItemServiceInterface $donationItemService,
        private DonationImagesRepositoryInterface $donationImagesRepository
    ) {}

    /**
     * @OA\Get(
     *     path="/api/donation-items",
     *     summary="Listar itens de doação",
     *     tags={"Donation Items"},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="location",
     *         in="query",
     *
     *         @OA\Schema(type="string", example="São Paulo")
     *     ),
     *
     *     @OA\Parameter(
     *         name="condition",
     *         in="query",
     *
     *         @OA\Schema(type="string", example="Usado - Bom estado")
     *     ),
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *
     *         @OA\Schema(type="string", example="sofá")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Lista de itens de doação",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/DonationItem"))
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['category_id', 'location', 'condition', 'search', 'latitude', 'longitude', 'radius']);
            $perPage = $request->get('per_page', 15);

            $items = $this->donationItemService->getAvailableItems($filters, $perPage);

            return response()->json($items);
        } catch (\Exception $e) {
            return $this->serverError($e, 'donation_item');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/donation-items",
     *     summary="Criar item de doação",
     *     tags={"Donation Items"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"title","description","category_id","condition","location"},
     *
     *             @OA\Property(property="title", type="string", example="Sofá 3 lugares"),
     *             @OA\Property(property="description", type="string", example="Sofá em bom estado, cor azul"),
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="condition", type="string", example="Usado - Bom estado"),
     *             @OA\Property(property="location", type="string", example="São Paulo, SP"),
     *             @OA\Property(property="latitude", type="number", example=-23.5505),
     *             @OA\Property(property="longitude", type="number", example=-46.6333),
     *             @OA\Property(property="images", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Item criado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Item criado com sucesso"),
     *             @OA\Property(property="data", ref="#/components/schemas/DonationItem")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function store(StoreDonationItemRequest $request): JsonResponse
    {
        try {
            $item = $this->donationItemService->create($request->user(), $request->validated());

            return response()->json([
                'message' => 'Item criado com sucesso',
                'data' => $item,
            ], 201);
        } catch (\Exception $e) {
            return $this->serverError($e, 'donation_item');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/donation-items/{id}",
     *     summary="Obter item específico",
     *     tags={"Donation Items"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Dados do item",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", ref="#/components/schemas/DonationItem"),
     *             @OA\Property(property="related_items", type="array", @OA\Items(ref="#/components/schemas/DonationItem"))
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Item não encontrado"
     *     )
     * )
     */
    public function show(DonationItem $donationItem): JsonResponse
    {
        try {
            $relatedItems = $this->donationItemService->getRelatedItems($donationItem);

            return response()->json([
                'data' => $donationItem,
                'related_items' => $relatedItems,
            ]);
        } catch (\Exception $e) {
            return $this->serverError($e, 'donation_item');
        }
    }

    /**
     * @OA\Put(
     *     path="/api/donation-items/{id}",
     *     summary="Atualizar item de doação",
     *     tags={"Donation Items"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="title", type="string", example="Sofá 3 lugares"),
     *             @OA\Property(property="description", type="string", example="Sofá em bom estado, cor azul"),
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="condition", type="string", example="Usado - Bom estado"),
     *             @OA\Property(property="location", type="string", example="São Paulo, SP"),
     *             @OA\Property(property="latitude", type="number", example=-23.5505),
     *             @OA\Property(property="longitude", type="number", example=-46.6333),
     *             @OA\Property(property="images", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Item atualizado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Item atualizado com sucesso"),
     *             @OA\Property(property="data", ref="#/components/schemas/DonationItem")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Sem permissão para modificar este item",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function update(UpdateDonationItemRequest $request, DonationItem $donationItem): JsonResponse
    {
        $this->authorize('update', $donationItem);

        try {
            $item = $this->donationItemService->update($donationItem, $request->user(), $request->validated());

            return response()->json([
                'message' => 'Item atualizado com sucesso',
                'data' => $item,
            ]);
        } catch (\Exception $e) {
            // Autorização real já foi tratada por authorize() (403) antes do try;
            // qualquer exceção aqui é erro inesperado de servidor.
            return $this->serverError($e, 'donation_item.update');
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/donation-items/{id}",
     *     summary="Excluir item de doação",
     *     tags={"Donation Items"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Item excluído com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Item excluído com sucesso")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Sem permissão para excluir este item",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function destroy(DonationItem $donationItem, Request $request): JsonResponse
    {
        $this->authorize('delete', $donationItem);

        // Regra de negócio (não é autorização nem erro de servidor): item doado
        // não pode ser excluído.
        if ($donationItem->status === 'donated') {
            return response()->json([
                'error' => 'Não é possível excluir um item que já foi doado.',
            ], 422);
        }

        try {
            $this->donationItemService->delete($donationItem, $request->user());

            return response()->json([
                'message' => 'Item excluído com sucesso',
            ]);
        } catch (\Exception $e) {
            // Autorização real já foi tratada por authorize() (403) antes do try.
            return $this->serverError($e, 'donation_item.destroy');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/my-donations",
     *     summary="Listar meus itens de doação",
     *     tags={"Donation Items"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Lista dos meus itens de doação",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/DonationItem"))
     *         )
     *     )
     * )
     */
    public function myDonations(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $items = $this->donationItemService->getUserItems($request->user(), $perPage);

            return response()->json($items);
        } catch (\Exception $e) {
            return $this->serverError($e, 'donation_item');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/donation-items/{donationItem}/images",
     *     summary="Upload de imagens para um item de doação",
     *     tags={"Donation Items"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="donationItem",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *
     *                 @OA\Property(property="images[]", type="array", @OA\Items(type="string", format="binary"))
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Imagens enviadas com sucesso"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Sem permissão"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Limite ou tipo inválido"
     *     )
     * )
     */
    public function uploadImages(UploadDonationImageRequest $request, DonationItem $donationItem): JsonResponse
    {
        if ($request->user()->id !== $donationItem->user_id) {
            return response()->json(['error' => 'Sem permissão.'], 403);
        }

        $existingCount = $this->donationImagesRepository->countByItemId($donationItem->id);
        $newCount = count($request->file('images', []));

        if ($existingCount + $newCount > 5) {
            return response()->json(['error' => 'Limite de 5 imagens por item excedido.'], 422);
        }

        $saved = [];
        foreach ($request->file('images') as $file) {
            $path = $file->store("donations/{$donationItem->id}", 'public');
            $image = $this->donationImagesRepository->create([
                'donation_item_id' => $donationItem->id,
                'path' => $path,
            ]);
            $saved[] = $image;
        }

        return response()->json(['message' => 'Imagens enviadas com sucesso', 'data' => $saved], 201);
    }
}
