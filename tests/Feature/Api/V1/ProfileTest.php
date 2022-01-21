<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use App\Models\User;
use App\Models\Skill;
use App\Models\Profile;
use App\Models\Category;
use Laravel\Sanctum\Sanctum;
use App\Models\SpokenLanguage;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /** @test */
    public function user_can_create_profile()
    {
        Sanctum::actingAs($this->user);

        $fake_data = Profile::factory()->definition();

        $avatar = UploadedFile::fake()->image('avatar.jpg');
        $username = 'new_username';

        $skills = [
            'skill_1', 'skill_2', 'skill_3', 'skill_4',
        ];

        $spoken_languages = [
            'language_1', 'language_2', 'language_3', 'language_4',
        ];

        $request_data = array_merge($fake_data, [
            'avatar' => $avatar,
            'skills' => $skills,
            'spoken_languages' => $spoken_languages,
            'username' => $username,
        ]);

        $resoponse = $this->json('post', route('v1.profile.store'), $request_data);
        $resoponse->assertOk()
            ->assertJsonStructure($this->expected_structure());

        $this->assertDatabaseCount('profiles', 1);
        $this->assertDatabaseHas('profiles', $fake_data + ['user_id' => $this->user->id]);

        $profile = Profile::first();

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'username' => $username,
        ]);

        $this->assertDatabaseCount('media', 1);
        $this->assertDatabaseHas('media', [
            'model_type' => Profile::class,
            'model_id' => $profile->id,
            'collection_name' => Profile::AVATAR_COLLECTION_NAME,
            'name' => 'avatar',
        ]);

        $this->assertDatabaseCount('skills', 4);
        foreach ($skills as $skill) {
            $this->assertDatabaseHas('skills', [
                'profile_id' => $profile->id,
                'name' => $skill,
            ]);
        }

        $this->assertDatabaseCount('spoken_languages', 4);
        foreach ($spoken_languages as $language) {
            $this->assertDatabaseHas('spoken_languages', [
                'profile_id' => $profile->id,
                'name' => $language,
            ]);
        }
    }

    /** @test */
    public function user_can_update_profile()
    {
        Sanctum::actingAs($this->user);

        $profile = Profile::factory()
            ->has(Skill::factory()->count(4))
            ->has(SpokenLanguage::factory()->count(4))
            ->create(['user_id' => $this->user->id]);

        $this->assertDatabaseCount('profiles', 1);
        $this->assertDatabaseCount('media', 0);
        $this->assertDatabaseCount('skills', 4);
        $this->assertDatabaseCount('spoken_languages', 4);

        $fake_data = Profile::factory()->definition();

        $avatar = UploadedFile::fake()->image('avatar.jpg');
        $username = 'new_username';

        $skills = [
            'skill_1', 'skill_2', 'skill_3', 'skill_4',
        ];

        $spoken_languages = [
            'language_1', 'language_2', 'language_3', 'language_4',
        ];

        $request_data = array_merge($fake_data, [
            'avatar' => $avatar,
            'skills' => $skills,
            'spoken_languages' => $spoken_languages,
            'username' => $username,
        ]);

        $resoponse = $this->json('put', route('v1.profile.store'), $request_data);
        $resoponse->assertOk()
            ->assertJsonStructure($this->expected_structure());

        $this->assertDatabaseCount('profiles', 1);
        $this->assertDatabaseHas('profiles', $fake_data + [
            'user_id' => $this->user->id,
            'id' => $profile->id,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'username' => $username,
        ]);

        $this->assertDatabaseCount('media', 1);
        $this->assertDatabaseHas('media', [
            'model_type' => Profile::class,
            'model_id' => Profile::first()->id,
            'collection_name' => Profile::AVATAR_COLLECTION_NAME,
            'name' => 'avatar',
        ]);

        $this->assertDatabaseCount('skills', 4);
        foreach ($skills as $skill) {
            $this->assertDatabaseHas('skills', [
                'profile_id' => $profile->id,
                'name' => $skill,
            ]);
        }

        $this->assertDatabaseCount('spoken_languages', 4);
        foreach ($spoken_languages as $language) {
            $this->assertDatabaseHas('spoken_languages', [
                'profile_id' => $profile->id,
                'name' => $language,
            ]);
        }
    }

    /** @test */
    public function user_can_get_profile()
    {
        Sanctum::actingAs($this->user);

        Profile::factory()
            ->has(Skill::factory()->count(4))
            ->has(SpokenLanguage::factory()->count(4))
            ->for(Category::factory(), 'category')
            ->for(Category::factory(), 'sub_category')
            ->create(['user_id' => $this->user->id]);

        $resoponse = $this->json('get', route('v1.profile.show'));

        $resoponse->assertOk()
            ->assertJsonStructure($this->expected_structure(true));
    }

    /** @test */
    public function user_can_complete_profile_step_2()
    {
        Sanctum::actingAs($this->user);

        Profile::factory()->create(['user_id' => $this->user->id]);
        Category::factory()
            ->has(Category::factory()->count(2), 'children')
            ->create();

        $request_data = [
            'description' => 'this is a berif description',
            'category_id' => 1,
            'sub_category_id' => 2,
            'video_presentation' => UploadedFile::fake()->create('video.mp4'),
            'portfolio' => UploadedFile::fake()->create('portfolio.pdf'),
        ];

        $resoponse = $this->json('put', route('v1.profile.store.step_2'), $request_data);

        $resoponse->assertOk()
            ->assertJsonStructure($this->expected_structure(true));

        $profile = Profile::first();

        $this->assertDatabaseHas('profiles', [
            'id' => $profile->id,
            'user_id' => $this->user->id,
            'description' => 'this is a berif description',
            'category_id' => 1,
            'sub_category_id' => 2,
        ]);

        $this->assertDatabaseHas('media', [
            'model_type' => Profile::class,
            'model_id' => $profile->id,
            'collection_name' => Profile::PRESENTATION_COLLECTION_NAME,
            'name' => 'video',
        ]);

        $this->assertDatabaseHas('media', [
            'model_type' => Profile::class,
            'model_id' => $profile->id,
            'collection_name' => Profile::PORTFOLIO_COLLECTION_NAME,
            'name' => 'portfolio',
        ]);
    }

    protected function expected_structure($has_category = false)
    {
        $categories = (! $has_category) ? [] : [
            'category' => [
                'id',
                'name',
            ],
            'sub_category' => [
                'id',
                'name',
            ],
        ];

        return [
            'first_name',
            'last_name',
            'nationality',
            'birth_date',
            'gender',
            'availability_on_demand',
            'per_hour',
            'avatar',
            'user' => [
                'id',
                'username',
                'email',
                'email_verified_at',
            ],
            'skills' => [
                '*' => [
                    'id',
                    'name',
                ],
            ],
            'spoken_languages' => [
                '*' => [
                    'id',
                    'name',
                ],
            ],
            'description',
            'video_presentation',
            'portfolio',
        ] + $categories;
    }
}
