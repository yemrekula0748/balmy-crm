<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;

/**
 * Otomatik üretildi: 2026-03-04 17:54:07
 * Çalıştır: php artisan db:seed --class=GeneratedDatabaseSeeder
 */
class GeneratedDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(GeneratedAssetCategoriesSeeder::class);
        $this->call(GeneratedAssetExitsSeeder::class);
        $this->call(GeneratedAssetsSeeder::class);
        $this->call(GeneratedBranchesSeeder::class);
        $this->call(GeneratedCarbonFootprintEntriesSeeder::class);
        $this->call(GeneratedCarbonFootprintReportsSeeder::class);
        $this->call(GeneratedContractComparisonsSeeder::class);
        $this->call(GeneratedDepartmentFaultTypeSeeder::class);
        $this->call(GeneratedDepartmentsSeeder::class);
        $this->call(GeneratedDoorLogsSeeder::class);
        $this->call(GeneratedFailedJobsSeeder::class);
        $this->call(GeneratedFaultAreasSeeder::class);
        $this->call(GeneratedFaultLocationsSeeder::class);
        $this->call(GeneratedFaultTypesSeeder::class);
        $this->call(GeneratedFaultUpdatesSeeder::class);
        $this->call(GeneratedFaultsSeeder::class);
        $this->call(GeneratedFoodLabelsSeeder::class);
        $this->call(GeneratedGuestLogsSeeder::class);
        $this->call(GeneratedPasswordResetTokensSeeder::class);
        $this->call(GeneratedPersonalAccessTokensSeeder::class);
        $this->call(GeneratedQrMenuCategoriesSeeder::class);
        $this->call(GeneratedQrMenuItemsSeeder::class);
        $this->call(GeneratedQrMenuLanguagesSeeder::class);
        $this->call(GeneratedQrMenusSeeder::class);
        $this->call(GeneratedRolePermissionsSeeder::class);
        $this->call(GeneratedRolesSeeder::class);
        $this->call(GeneratedStaffSurveyAnswersSeeder::class);
        $this->call(GeneratedStaffSurveyQuestionsSeeder::class);
        $this->call(GeneratedStaffSurveyResponsesSeeder::class);
        $this->call(GeneratedStaffSurveysSeeder::class);
        $this->call(GeneratedSurveyAnswersSeeder::class);
        $this->call(GeneratedSurveyQuestionsSeeder::class);
        $this->call(GeneratedSurveyResponsesSeeder::class);
        $this->call(GeneratedSurveysSeeder::class);
        $this->call(GeneratedUserRolesSeeder::class);
        $this->call(GeneratedUsersSeeder::class);
        $this->call(GeneratedVehicleInsurancesSeeder::class);
        $this->call(GeneratedVehicleMaintenancesSeeder::class);
        $this->call(GeneratedVehicleOperationsSeeder::class);
        $this->call(GeneratedVehiclesSeeder::class);
    }
}