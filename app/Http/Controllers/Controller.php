<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Doe Me API",
 *     version="1.0.0",
 *     description="API para o aplicativo Doe Me - plataforma de doações que conecta pessoas dispostas a doar bens usados com pessoas que precisam deles",
 *     @OA\Contact(
 *         email="contato@doeme.com",
 *         name="Equipe Doe Me"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Servidor de desenvolvimento"
 * )
 * 
 * @OA\Server(
 *     url="https://api.doeme.com",
 *     description="Servidor de produção"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Token de autenticação Bearer"
 * )
 * 
 * @OA\Schema(
 *     schema="Category",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Móveis"),
 *     @OA\Property(property="slug", type="string", example="moveis"),
 *     @OA\Property(property="description", type="string", example="Móveis para casa e escritório"),
 *     @OA\Property(property="icon", type="string", example="furniture"),
 *     @OA\Property(property="active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="João Silva"),
 *     @OA\Property(property="email", type="string", example="joao@example.com"),
 *     @OA\Property(property="phone", type="string", example="(11) 99999-9999"),
 *     @OA\Property(property="location", type="string", example="São Paulo, SP"),
 *     @OA\Property(property="avatar", type="string", example="https://example.com/avatar.jpg"),
 *     @OA\Property(property="average_rating", type="number", format="float", example=4.5),
 *     @OA\Property(property="total_reviews", type="integer", example=10),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="DonationItem",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Sofá 3 lugares"),
 *     @OA\Property(property="description", type="string", example="Sofá em bom estado, cor azul"),
 *     @OA\Property(property="condition", type="string", example="Usado - Bom estado"),
 *     @OA\Property(property="location", type="string", example="São Paulo, SP"),
 *     @OA\Property(property="latitude", type="number", format="float", example=-23.5505),
 *     @OA\Property(property="longitude", type="number", format="float", example=-46.6333),
 *     @OA\Property(property="status", type="string", enum={"available", "reserved", "donated"}, example="available"),
 *     @OA\Property(property="images", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="user", ref="#/components/schemas/User"),
 *     @OA\Property(property="category", ref="#/components/schemas/Category"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="Error",
 *     type="object",
 *     @OA\Property(property="error", type="string", example="Mensagem de erro"),
 *     @OA\Property(property="messages", type="object", example={"field": {"Mensagem de validação"}})
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
