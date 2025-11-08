<?php

namespace App\Usecase\Contracts;

interface DashboardUsecaseInterface
{
    /**
     * Get dashboard data based on user role
     *
     * @param string $userUuid
     * @return array
     */
    public function getDashboardData(string $userUuid): array;

    /**
     * Get admin dashboard data
     *
     * @param string $userUuid
     * @return array
     */
    public function getAdminDashboard(string $userUuid): array;

    /**
     * Get owner dashboard data
     *
     * @param string $userUuid
     * @return array
     */
    public function getOwnerDashboard(string $userUuid): array;

    /**
     * Get employee dashboard data
     *
     * @param string $userUuid
     * @return array
     */
    public function getEmployeeDashboard(string $userUuid): array;

    /**
     * Get expenditures for admin/owner
     *
     * @param array $filters
     * @return array
     */
    public function getExpenditures(array $filters = []): array;

    /**
     * Approve expenditure
     *
     * @param string $expenditureUuid
     * @param string $approverUuid
     * @return array
     */
    public function approveExpenditure(string $expenditureUuid, string $approverUuid): array;

    /**
     * Generate reports for admin/owner
     *
     * @param array $filters
     * @return array
     */
    public function generateReports(array $filters = []): array;

    /**
     * Get dashboard statistics
     *
     * @param string $userUuid
     * @return array
     */
    public function getDashboardStatistics(string $userUuid): array;
}