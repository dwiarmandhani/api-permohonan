<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test registration of a new user.
     */
    public function test_user_can_register()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'testuser@example.com',
            'phone' => '1234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'user' => [
                         'id',
                         'name',
                         'username',
                         'email',
                         'phone',
                         'created_at',
                         'updated_at',
                     ],
                 ]);
    }

    /**
     * Test login with email or username.
     */
    public function test_user_can_login_with_email_or_username()
    {
        $user = User::factory()->create([
            'name' => 'Kyle Mayert',
            'email' => 'testuser@example.com',
            'password' => Hash::make('password'), // Use Hash::make for password
            'username' => 'testuser',
            'phone' => '123-456-7890',
        ]);

        // Test login with email
        $response = $this->postJson('/api/login', [
            'email' => 'testuser@example.com',
            'password' => 'password',
        ]);
        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'access_token', 'token_type', 'expires_in']);

        // Test login with username
        $response = $this->postJson('/api/login', [
            'email' => 'testuser', // Using username field
            'password' => 'password',
        ]);
        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'access_token', 'token_type', 'expires_in']);
    }

    /**
     * Test update profile of a user.
     */
    public function test_user_can_update_profile()
    {
        $user = User::factory()->create([
            'name' => 'Kyle Mayert',
            'email' => 'testuser@example.com',
            'password' => Hash::make('password'), // Use Hash::make for password
            'username' => 'testuser',
            'phone' => '123-456-7890',
        ]);

        $response = $this->actingAs($user, 'api')->putJson('/api/profile', [
            'name' => 'Updated User',
            'username' => 'updateduser',
            'email' => 'updateduser@example.com',
            'phone' => '0987654321',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Profile successfully updated',
                 ]);
    }

    /**
     * Test changing password of a user.
     */
    public function test_user_can_change_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        $response = $this->actingAs($user, 'api')->postJson('/api/change-password', [
            'current_password' => 'oldpassword',
            'new_password' => 'newpassword',
            'new_password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Password successfully changed',
                 ]);

        // Verify that the old password no longer works
        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'oldpassword',
        ])->assertStatus(401);

        // Verify that the new password works
        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'newpassword',
        ])->assertStatus(200);
    }

    public function test_registration_fails_with_missing_fields()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'username' => 'testuser',
            // Email and password are missing
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'errors' => ['email', 'password']
                 ]);
    }

    /**
     * Negative case for registration with non-unique email and username.
     */
    public function test_registration_fails_with_duplicate_email_and_username()
    {
        User::factory()->create([
            'email' => 'duplicate@example.com',
            'username' => 'duplicateuser',
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'Another User',
            'username' => 'duplicateuser', // Duplicate username
            'email' => 'duplicate@example.com', // Duplicate email
            'phone' => '0987654321',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'errors' => ['email', 'username']
                 ]);
    }

    /**
     * Negative case for login with invalid credentials.
     */
    public function test_login_fails_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'testuser@example.com',
            'username' => 'testuser',
            'password' => Hash::make('correctpassword'),
        ]);

        // Attempt login with wrong password
        $response = $this->postJson('/api/login', [
            'email' => 'testuser@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['error' => 'Invalid Credentials']);
    }

    /**
     * Negative case for updating profile with existing email or username.
     */
    public function test_update_profile_fails_with_duplicate_email_or_username()
    {
        $user1 = User::factory()->create([
            'name' => 'Kyle Mayert',
            'email' => 'testuser@example.com',
            'password' => Hash::make('password'), // Use Hash::make for password
            'username' => 'testuser',
            'phone' => '123-456-7891',
        ]);

        $user2 = User::factory()->create([
            'name' => 'Kyle Mayert 2',
            'email' => 'testuser2@example.com',
            'password' => Hash::make('password'), // Use Hash::make for password
            'username' => 'testuser2',
            'phone' => '123-456-7890',
        ]);

        // Attempt to update user2's profile with user1's email and username
        $response = $this->actingAs($user2, 'api')->putJson('/api/profile', [
            'name' => 'Updated User',
            'username' => 'testuser', // Duplicate username
            'email' => 'testuser@example.com', // Duplicate email
            'phone' => '0987654321',
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'errors' => ['email', 'username']
                 ]);
    }

    /**
     * Negative case for changing password with incorrect current password.
     */
    public function test_change_password_fails_with_incorrect_current_password()
    {
        $user = User::factory()->create([
            'name' => 'Kyle Mayert 2',
            'email' => 'testuser2@example.com',
            'password' => Hash::make('password'), // Use Hash::make for password
            'username' => 'testuser2',
            'phone' => '123-456-7890',
        ]);

        // Attempt to change password with an incorrect current password
        $response = $this->actingAs($user, 'api')->postJson('/api/change-password', [
            'current_password' => 'wrongpassword', // Incorrect current password
            'new_password' => 'newpassword',
            'new_password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(401)
             ->assertJson([
                 'success' => false,
                 'message' => 'Current password is incorrect'
             ]);
    }

    /**
     * Negative case for changing password with unmatched new password confirmation.
     */
    public function test_change_password_fails_with_unmatched_password_confirmation()
    {
        $user = User::factory()->create([
            'password' => Hash::make('correctpassword'),
        ]);

        $response = $this->actingAs($user, 'api')->postJson('/api/change-password', [
            'current_password' => 'correctpassword',
            'new_password' => 'newpassword',
            'new_password_confirmation' => 'differentpassword', // Does not match
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'errors' => ['new_password']
                 ]);
    }
}
