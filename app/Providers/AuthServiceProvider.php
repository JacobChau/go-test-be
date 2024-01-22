<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Assessment;
use App\Models\Group;
use App\Models\Passage;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use App\Policies\AssessmentPolicy;
use App\Policies\GroupPolicy;
use App\Policies\PassagePolicy;
use App\Policies\QuestionPolicy;
use App\Policies\SubjectPolicy;
use App\Policies\UserPolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Subject::class => SubjectPolicy::class,
        Passage::class => PassagePolicy::class,
        Question::class => QuestionPolicy::class,
        Assessment::class => AssessmentPolicy::class,
        Group::class => GroupPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            return config('app.frontend_url').'/reset-password?token='.$token.'&email='.$notifiable->getEmailForPasswordReset();
        });
    }
}
