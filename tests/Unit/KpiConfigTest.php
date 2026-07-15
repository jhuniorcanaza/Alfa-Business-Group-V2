<?php

use App\Models\KpiConfig;

test('getTrafficLightColor returns correct colors based on percentage compliance', function () {
    $kpi = new KpiConfig([
        'weekly_goal' => 3,
        'yellow_threshold_pct' => 70,
        'red_threshold_pct' => 40,
    ]);

    // 3 out of 3 (100%) -> green
    expect($kpi->getTrafficLightColor(3))->toBe('green');
    // 4 out of 3 (133.3%) -> green
    expect($kpi->getTrafficLightColor(4))->toBe('green');

    // 2 out of 3 (66.6%) -> yellow (since it's > 40% and < 100%)
    expect($kpi->getTrafficLightColor(2))->toBe('yellow');

    // 1 out of 3 (33.3%) -> red (since it's <= 40%)
    expect($kpi->getTrafficLightColor(1))->toBe('red');
    // 0 out of 3 (0%) -> red (since it's <= 40%)
    expect($kpi->getTrafficLightColor(0))->toBe('red');
});

test('getTrafficLightColor returns green if weekly_goal is 0 or less', function () {
    $kpi = new KpiConfig([
        'weekly_goal' => 0,
        'yellow_threshold_pct' => 70,
        'red_threshold_pct' => 40,
    ]);

    expect($kpi->getTrafficLightColor(5))->toBe('green');
});
