<?php

namespace App\Providers;

use App\Models\AppSetting;
use App\Models\Outsider;
use App\Observers\OutsiderObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Interface\UserRepository;
use App\Repositories\Interface\ShiftRepository;
use App\Repositories\Interface\InternRepository;
use App\Repositories\Interface\OfficeRepository;
use App\Repositories\Interface\QuotesRepository;
use App\Repositories\Interface\SchoolRepository;
use App\Repositories\Interface\HolidayRepository;
use App\Repositories\Interface\ProfileRepository;
use App\Repositories\Interface\ProjectRepository;
use App\Repositories\Interface\DivisionRepository;
use App\Repositories\Interface\ScheduleRepository;
use App\Repositories\Interface\AttendanceRepository;
use App\Repositories\Interface\CoordinateRepository;
use App\Repositories\Interface\LogActivityRepository;
use App\Repositories\Interface\DiscountTimeRepository;
use App\Repositories\Interface\PermitReasonRepository;
use App\Repositories\Implementation\UserRepositoryIMPL;
use App\Repositories\Interface\DetailProjectRepository;
use App\Repositories\Implementation\ShiftRepositoryIMPL;
use App\Repositories\Interface\AdjustableAttdRepository;
use App\Repositories\Interface\DetailScheduleRepository;
use App\Repositories\Interface\PermitCategoryRepository;
use App\Repositories\Implementation\InternRepositoryIMPL;
use App\Repositories\Implementation\OfficeRepositoryIMPL;
use App\Repositories\Implementation\QuotesRepositoryIMPL;
use App\Repositories\Implementation\SchoolRepositoryIMPL;
use App\Repositories\Implementation\HolidayRepositoryIMPL;
use App\Repositories\Implementation\ProfileRepositoryIMPL;
use App\Repositories\Implementation\ProjectRepositoryIMPL;
use App\Repositories\Implementation\DivisionRepositoryIMPL;
use App\Repositories\Implementation\ScheduleRepositoryIMPL;
use App\Repositories\Implementation\AttendanceRepositoryIMPL;
use App\Repositories\Implementation\CoordinateRepositoryIMPL;
use App\Repositories\Implementation\LogActivityRepositoryIMPL;
use App\Repositories\Implementation\DiscountTimeRepositoryIMPL;
use App\Repositories\Implementation\PermitReasonRepositoryIMPL;
use App\Repositories\Implementation\DetailProjectRepositoryIMPL;
use App\Repositories\Implementation\AdjustableAttdRepositoryIMPL;
use App\Repositories\Implementation\DetailScheduleRepositoryIMPL;
use App\Repositories\Implementation\PermitCategoryRepositoryIMPL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(UserRepository::class, UserRepositoryIMPL::class);
        $this->app->singleton(ProfileRepository::class, ProfileRepositoryIMPL::class);
        $this->app->singleton(SchoolRepository::class, SchoolRepositoryIMPL::class);
        $this->app->singleton(InternRepository::class, InternRepositoryIMPL::class);
        $this->app->singleton(AttendanceRepository::class, AttendanceRepositoryIMPL::class);
        $this->app->singleton(ShiftRepository::class, ShiftRepositoryIMPL::class);
        $this->app->singleton(LogActivityRepository::class, LogActivityRepositoryIMPL::class);
        $this->app->singleton(DivisionRepository::class, DivisionRepositoryIMPL::class);
        $this->app->singleton(QuotesRepository::class, QuotesRepositoryIMPL::class);
        $this->app->singleton(OfficeRepository::class, OfficeRepositoryIMPL::class);
        $this->app->singleton(ScheduleRepository::class, ScheduleRepositoryIMPL::class);
        $this->app->singleton(DetailScheduleRepository::class, DetailScheduleRepositoryIMPL::class);
        $this->app->singleton(PermitReasonRepository::class, PermitReasonRepositoryIMPL::class);
        $this->app->singleton(PermitCategoryRepository::class, PermitCategoryRepositoryIMPL::class);
        $this->app->singleton(ProjectRepository::class, ProjectRepositoryIMPL::class);
        $this->app->singleton(DetailProjectRepository::class, DetailProjectRepositoryIMPL::class);
        $this->app->singleton(CoordinateRepository::class, CoordinateRepositoryIMPL::class);
        $this->app->singleton(HolidayRepository::class, HolidayRepositoryIMPL::class);
        $this->app->singleton(DiscountTimeRepository::class, DiscountTimeRepositoryIMPL::class);
        $this->app->singleton(AdjustableAttdRepository::class, AdjustableAttdRepositoryIMPL::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Outsider::observe(OutsiderObserver::class);

        View::composer('*', function ($view) {
            try {
                $view->with('appSetting', AppSetting::getSettings());
            } catch (\Throwable) {
                // Fallback for migrations or initial setup
            }
        });
    }
}
