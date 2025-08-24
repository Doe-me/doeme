<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Contracts\Services\ReviewServiceInterface;
use App\Http\Requests\Api\StoreReviewRequest;
use App\Http\Requests\Api\UpdateReviewRequest;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Reviews",
 *     description="Endpoints para gerenciamento de avaliações"
 * )
 */
class ReviewController extends Controller
{
    public function __construct(
        private ReviewServiceInterface $reviewService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/reviews",
     *     summary="Listar avaliações",
     *     tags={"Reviews"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="reviewed_user_id",
     *         in="query",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="rating",
     *         in="query",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de avaliações",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="rating", type="integer"),
     *                 @OA\Property(property="comment", type="string"),
     *                 @OA\Property(property="reviewer", ref="#/components/schemas/User"),
     *                 @OA\Property(property="reviewed_user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="donation_item", ref="#/components/schemas/DonationItem"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             ))
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['reviewed_user_id', 'reviewer_id', 'rating']);
            $perPage = $request->get('per_page', 15);
            
            $reviews = $this->reviewService->getReviews($filters, $perPage);

            return response()->json($reviews);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/reviews",
     *     summary="Criar avaliação",
     *     tags={"Reviews"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"donation_item_id","reviewed_user_id","rating"},
     *             @OA\Property(property="donation_item_id", type="integer", example=1),
     *             @OA\Property(property="reviewed_user_id", type="integer", example=2),
     *             @OA\Property(property="rating", type="integer", example=5),
     *             @OA\Property(property="comment", type="string", example="Excelente pessoa, muito educada!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Avaliação criada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Avaliação criada com sucesso"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="rating", type="integer"),
     *                 @OA\Property(property="comment", type="string"),
     *                 @OA\Property(property="reviewer", ref="#/components/schemas/User"),
     *                 @OA\Property(property="reviewed_user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="donation_item", ref="#/components/schemas/DonationItem")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro ao criar avaliação",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function store(StoreReviewRequest $request): JsonResponse
    {
        try {
            $review = $this->reviewService->create($request->user(), $request->validated());

            return response()->json([
                'message' => 'Avaliação criada com sucesso',
                'data' => $review
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao criar avaliação',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/reviews/{id}",
     *     summary="Obter avaliação específica",
     *     tags={"Reviews"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dados da avaliação",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="rating", type="integer"),
     *                 @OA\Property(property="comment", type="string"),
     *                 @OA\Property(property="reviewer", ref="#/components/schemas/User"),
     *                 @OA\Property(property="reviewed_user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="donation_item", ref="#/components/schemas/DonationItem"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Avaliação não encontrada"
     *     )
     * )
     */
    public function show(Review $review): JsonResponse
    {
        return response()->json(['data' => $review]);
    }

    /**
     * @OA\Put(
     *     path="/api/reviews/{id}",
     *     summary="Atualizar avaliação",
     *     tags={"Reviews"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="rating", type="integer", example=4),
     *             @OA\Property(property="comment", type="string", example="Boa pessoa, recomendo!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Avaliação atualizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Avaliação atualizada com sucesso"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="rating", type="integer"),
     *                 @OA\Property(property="comment", type="string"),
     *                 @OA\Property(property="reviewer", ref="#/components/schemas/User"),
     *                 @OA\Property(property="reviewed_user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="donation_item", ref="#/components/schemas/DonationItem")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Sem permissão para modificar esta avaliação",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function update(UpdateReviewRequest $request, Review $review): JsonResponse
    {
        try {
            $review = $this->reviewService->update($review, $request->user(), $request->validated());

            return response()->json([
                'message' => 'Avaliação atualizada com sucesso',
                'data' => $review
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao atualizar avaliação',
                'message' => $e->getMessage()
            ], 403);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/reviews/{id}",
     *     summary="Excluir avaliação",
     *     tags={"Reviews"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Avaliação excluída com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Avaliação excluída com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Sem permissão para excluir esta avaliação",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function destroy(Review $review, Request $request): JsonResponse
    {
        try {
            $this->reviewService->delete($review, $request->user());

            return response()->json([
                'message' => 'Avaliação excluída com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao excluir avaliação',
                'message' => $e->getMessage()
            ], 403);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}/reviews",
     *     summary="Obter avaliações de um usuário",
     *     tags={"Reviews"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Avaliações e estatísticas do usuário",
     *         @OA\JsonContent(
     *             @OA\Property(property="reviews", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="rating", type="integer"),
     *                     @OA\Property(property="comment", type="string"),
     *                     @OA\Property(property="reviewer", ref="#/components/schemas/User"),
     *                     @OA\Property(property="donation_item", ref="#/components/schemas/DonationItem"),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 ))
     *             ),
     *             @OA\Property(property="stats", type="object",
     *                 @OA\Property(property="total_reviews", type="integer"),
     *                 @OA\Property(property="average_rating", type="number"),
     *                 @OA\Property(property="rating_distribution", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function userReviews(User $user, Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $result = $this->reviewService->getUserReviews($user, $perPage);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

