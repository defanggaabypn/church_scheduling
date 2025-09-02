<?php
class GeneticAlgorithm {
    private $conn;
    private $population_size = 50;
    private $max_generations = 100;
    private $crossover_rate = 0.8;
    private $mutation_rate = 0.2;
    private $elitism_count = 5;
    
    private $bookings = [];
    private $fixed_schedules = [];
    private $approved_bookings = [];
    private $available_time_slots = []; // Slot waktu yang tersedia
    
    public function __construct($db) {
        $this->conn = $db;
        // Generate slot waktu dari jam 8 pagi hingga 10 malam dengan interval 30 menit
        $this->available_time_slots = $this->generateTimeSlots("08:00", "22:00", 30);
    }
    
    // Generate slot waktu yang tersedia
    private function generateTimeSlots($start_time, $end_time, $interval_minutes) {
        $slots = [];
        $current = strtotime($start_time);
        $end = strtotime($end_time);
        
        while ($current < $end) {
            $slots[] = date('H:i', $current);
            $current += $interval_minutes * 60;
        }
        
        return $slots;
    }
    
    // Menghitung durasi antara dua waktu dalam menit
    private function calculateDuration($start_time, $end_time) {
        $start = strtotime($start_time);
        $end = strtotime($end_time);
        return ($end - $start) / 60; // Konversi detik ke menit
    }
    
    // Menambahkan menit ke waktu
    private function addMinutesToTime($time, $minutes) {
        $timestamp = strtotime($time);
        $new_timestamp = $timestamp + ($minutes * 60);
        return date('H:i', $new_timestamp);
    }
    
    // Load bookings and fixed schedules
    public function load_data($start_date, $end_date) {
        // Load pending bookings
        $query = 'SELECT * FROM bookings WHERE status = "pending" AND date BETWEEN :start_date AND :end_date';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        $this->bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Load fixed schedules
        $query = 'SELECT * FROM schedules WHERE is_fixed = 1 AND date BETWEEN :start_date AND :end_date';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        $this->fixed_schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Load approved bookings
        $query = 'SELECT * FROM bookings WHERE status = "approved" AND date BETWEEN :start_date AND :end_date';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        $this->approved_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Initialize population with alternative time slots
    private function initialize_population() {
        $population = [];
        
        for ($i = 0; $i < $this->population_size; $i++) {
            $chromosome = [];
            
            foreach ($this->bookings as $booking) {
                // Untuk setiap booking, tambahkan 2 gen:
                // Gen 1: apakah booking dimasukkan (1) atau tidak (0)
                // Gen 2: slot waktu alternatif yang digunakan (0 = waktu asli, 1+ = slot alternatif)
                $chromosome[] = rand(0, 1); // Include/exclude
                $chromosome[] = rand(0, count($this->available_time_slots) - 1); // Slot waktu
            }
            
            $population[] = $chromosome;
        }
        
        return $population;
    }
    
    // Calculate fitness for a chromosome with alternative time slots
    private function calculate_fitness($chromosome) {
        $fitness = 0;
        $scheduled_events = [];
        $final_schedule = [];
        
        // Add fixed schedules to scheduled events
        foreach ($this->fixed_schedules as $schedule) {
            $scheduled_events[] = [
                'id' => 'fixed_' . $schedule['id'],
                'title' => $schedule['title'],
                'date' => $schedule['date'],
                'start_time' => $schedule['start_time'],
                'end_time' => $schedule['end_time'],
                'type' => 'fixed'
            ];
        }
        
        // Add approved bookings to scheduled events
        foreach ($this->approved_bookings as $booking) {
            $scheduled_events[] = [
                'id' => 'approved_' . $booking['id'],
                'title' => $booking['title'],
                'date' => $booking['date'],
                'start_time' => $booking['start_time'],
                'end_time' => $booking['end_time'],
                'type' => 'approved'
            ];
        }
        
        // Check which bookings are included in this chromosome
        for ($i = 0; $i < count($this->bookings); $i++) {
            $include_index = $i * 2;
            $slot_index = $i * 2 + 1;
            
            if ($chromosome[$include_index] == 1) { // Booking dimasukkan dalam jadwal
                $booking = $this->bookings[$i];
                $original_duration = $this->calculateDuration($booking['start_time'], $booking['end_time']); // Dalam menit
                
                // Tentukan slot waktu yang digunakan (original atau alternatif)
                $time_slot_id = $chromosome[$slot_index] % count($this->available_time_slots);
                $alternative_start_time = $this->available_time_slots[$time_slot_id];
                $alternative_end_time = $this->addMinutesToTime($alternative_start_time, $original_duration);
                
                // Gunakan waktu alternatif jika chromosome menunjukkan slot_id > 0
                $start_time = ($time_slot_id > 0) ? $alternative_start_time : $booking['start_time'];
                $end_time = ($time_slot_id > 0) ? $alternative_end_time : $booking['end_time'];
                
                $has_conflict = false;
                $conflict_severity = 0;
                
                // Check for conflicts with already scheduled events
                foreach ($scheduled_events as $event) {
                    if ($booking['date'] == $event['date']) {
                        // Check if times overlap
                        if (
                            ($start_time < $event['end_time'] && $end_time > $event['start_time'])
                        ) {
                            $has_conflict = true;
                            
                            // Hitung tingkat keparahan konflik berdasarkan durasi overlap
                            $overlap_start = max($start_time, $event['start_time']);
                            $overlap_end = min($end_time, $event['end_time']);
                            $overlap_duration = $this->calculateDuration($overlap_start, $overlap_end);
                            $total_duration = $this->calculateDuration($start_time, $end_time);
                            $conflict_severity += ($overlap_duration / $total_duration);
                            
                            break;
                        }
                    }
                }
                
                if (!$has_conflict) {
                    // No conflict, add to scheduled events and increase fitness
                    $event_data = [
                        'id' => 'pending_' . $booking['id'],
                        'title' => $booking['title'],
                        'date' => $booking['date'],
                        'start_time' => $start_time,
                        'end_time' => $end_time,
                        'type' => 'pending',
                        'is_alternative' => ($time_slot_id > 0)
                    ];
                    
                    $scheduled_events[] = $event_data;
                    $final_schedule[] = array_merge($booking, [
                        'scheduled_start_time' => $start_time,
                        'scheduled_end_time' => $end_time,
                        'is_alternative' => ($time_slot_id > 0)
                    ]);
                    
                    // Berikan bonus fitness untuk jadwal yang berhasil dimasukkan
                    $fitness += 1;
                    
                    // Berikan penalti kecil jika menggunakan slot waktu alternatif
                    if ($time_slot_id > 0) {
                        $fitness -= 0.2; // Penalti untuk mengubah waktu asli
                    }
                } else {
                    // Conflict, decrease fitness based on severity
                    $fitness -= $conflict_severity * 0.5;
                }
            } else {
                // Berikan penalti kecil untuk booking yang tidak dimasukkan
                $fitness -= 0.1;
            }
        }
        
        return [
            'fitness' => $fitness,
            'schedule' => $final_schedule
        ];
    }
    
    // Select parents using tournament selection
    private function select_parents($population, $fitness_scores) {
        $tournament_size = 3;
        $parents = [];
        
        for ($i = 0; $i < 2; $i++) {
            $tournament_indices = array_rand($population, min($tournament_size, count($population)));
            
            // Handle case when tournament_indices is not an array (happens when tournament_size = 1)
            if (!is_array($tournament_indices)) {
                $tournament_indices = [$tournament_indices];
            }
            
            $best_index = $tournament_indices[0];
            $best_fitness = $fitness_scores[$best_index];
            
            foreach ($tournament_indices as $index) {
                if ($fitness_scores[$index] > $best_fitness) {
                    $best_index = $index;
                    $best_fitness = $fitness_scores[$index];
                }
            }
            
            $parents[] = $population[$best_index];
        }
        
        return $parents;
    }
    
    // Perform crossover for chromosomes with alternative time slots
    private function crossover($parent1, $parent2) {
        if (rand(0, 100) / 100 > $this->crossover_rate) {
            return [$parent1, $parent2];
        }
        
        $crossover_point = rand(0, count($parent1) - 1);
        
        $child1 = array_merge(
            array_slice($parent1, 0, $crossover_point),
            array_slice($parent2, $crossover_point)
        );
        
        $child2 = array_merge(
            array_slice($parent2, 0, $crossover_point),
            array_slice($parent1, $crossover_point)
        );
        
        return [$child1, $child2];
    }
    
    // Perform mutation for chromosomes with alternative time slots
    private function mutate($chromosome) {
        for ($i = 0; $i < count($chromosome); $i++) {
            if (rand(0, 100) / 100 < $this->mutation_rate) {
                // Untuk gen include/exclude (indeks genap), flip antara 0 dan 1
                if ($i % 2 == 0) {
                    $chromosome[$i] = 1 - $chromosome[$i]; // Flip 0 jadi 1, atau 1 jadi 0
                } else {
                    // Untuk gen slot waktu (indeks ganjil), pilih slot waktu acak
                    $chromosome[$i] = rand(0, count($this->available_time_slots) - 1);
                }
            }
        }
        
        return $chromosome;
    }
    
    // Run the genetic algorithm with alternative time slots
    public function optimize() {
        $population = $this->initialize_population();
        $best_chromosome = null;
        $best_fitness = -1;
        $best_schedule = [];
        
        for ($generation = 0; $generation < $this->max_generations; $generation++) {
            // Calculate fitness for each chromosome
            $fitness_scores = [];
            $schedules = [];
            
            foreach ($population as $chromosome) {
                $result = $this->calculate_fitness($chromosome);
                $fitness_scores[] = $result['fitness'];
                $schedules[] = $result['schedule'];
                
                if ($result['fitness'] > $best_fitness) {
                    $best_fitness = $result['fitness'];
                    $best_chromosome = $chromosome;
                    $best_schedule = $result['schedule'];
                }
            }
            
            // Create new population
            $new_population = [];
            
            // Elitism - keep best chromosomes
            $combined = array_combine(range(0, count($population) - 1), $fitness_scores);
            arsort($combined);
            $elite_indices = array_slice(array_keys($combined), 0, $this->elitism_count, true);
            
            foreach ($elite_indices as $index) {
                $new_population[] = $population[$index];
            }
            
            // Create rest of the population through selection, crossover, and mutation
            while (count($new_population) < $this->population_size) {
                $parents = $this->select_parents($population, $fitness_scores);
                $children = $this->crossover($parents[0], $parents[1]);
                
                $children[0] = $this->mutate($children[0]);
                $children[1] = $this->mutate($children[1]);
                
                $new_population[] = $children[0];
                
                if (count($new_population) < $this->population_size) {
                    $new_population[] = $children[1];
                }
            }
            
            $population = $new_population;
        }
        
        // Return the best solution
        return $best_schedule;
    }
    
    // New method: Run the genetic algorithm with detailed process information and alternative time slots
    public function optimize_with_details() {
        $start_time = microtime(true);
        
        $population = $this->initialize_population();
        $best_chromosome = null;
        $best_fitness = -1;
        $best_schedule = [];
        $generation_data = [];
        
        for ($generation = 0; $generation < $this->max_generations; $generation++) {
            // Calculate fitness for each chromosome
            $fitness_scores = [];
            $schedules = [];
            $total_fitness = 0;
            
            foreach ($population as $chromosome) {
                $result = $this->calculate_fitness($chromosome);
                $fitness_scores[] = $result['fitness'];
                $schedules[] = $result['schedule'];
                $total_fitness += $result['fitness'];
                
                if ($result['fitness'] > $best_fitness) {
                    $best_fitness = $result['fitness'];
                    $best_chromosome = $chromosome;
                    $best_schedule = $result['schedule'];
                }
            }
            
            // Calculate average fitness
            $avg_fitness = count($population) > 0 ? $total_fitness / count($population) : 0;
            
            // Count optimal bookings in best schedule
            $optimal_count = count($best_schedule);
            
            // Store generation data
            $generation_data[$generation] = [
                'best_fitness' => $best_fitness,
                'avg_fitness' => $avg_fitness,
                'optimal_count' => $optimal_count
            ];
            
            // Create new population
            $new_population = [];
            
            // Elitism - keep best chromosomes
            $combined = array_combine(range(0, count($population) - 1), $fitness_scores);
            arsort($combined);
            $elite_indices = array_slice(array_keys($combined), 0, $this->elitism_count, true);
            
            foreach ($elite_indices as $index) {
                $new_population[] = $population[$index];
            }
            
            // Create rest of the population through selection, crossover, and mutation
            while (count($new_population) < $this->population_size) {
                $parents = $this->select_parents($population, $fitness_scores);
                $children = $this->crossover($parents[0], $parents[1]);
                
                $children[0] = $this->mutate($children[0]);
                $children[1] = $this->mutate($children[1]);
                
                $new_population[] = $children[0];
                
                if (count($new_population) < $this->population_size) {
                    $new_population[] = $children[1];
                }
            }
            
            $population = $new_population;
            
            // Check for convergence (if best fitness hasn't improved for 10 generations)
            if ($generation > 10) {
                $converged = true;
                for ($i = $generation - 10; $i < $generation; $i++) {
                    if ($generation_data[$i]['best_fitness'] < $best_fitness) {
                        $converged = false;
                        break;
                    }
                }
                
                if ($converged) {
                    break; // Stop if converged
                }
            }
        }
        
        $end_time = microtime(true);
        $execution_time = round($end_time - $start_time, 4);
        
        // Calculate convergence rate
        $initial_fitness = isset($generation_data[0]) ? $generation_data[0]['best_fitness'] : 0;
        $final_fitness = $best_fitness;
        $convergence_rate = $initial_fitness != 0 ? round(($final_fitness - $initial_fitness) / abs($initial_fitness) * 100, 2) : 0;
        
        // Simpan informasi booking untuk visualisasi
        $pending_bookings = [];
        foreach ($this->bookings as $booking) {
            $pending_bookings[] = $booking;
        }
        
        // Tambahkan informasi tentang proses evolusi kromosom
        $evolution_process = [];
        $selected_generations = [0]; // Generasi pertama
        
        // Tambahkan beberapa generasi di tengah jika tersedia
        if (count($generation_data) > 10) {
            $middle_gen = floor(count($generation_data) / 2);
            $selected_generations[] = $middle_gen;
        }
        
        // Tambahkan generasi terakhir
        if (count($generation_data) > 1) {
            $selected_generations[] = count($generation_data) - 1;
        }
        
        // Kumpulkan data kromosom untuk generasi yang dipilih
        foreach ($selected_generations as $gen) {
            if (isset($generation_data[$gen])) {
                $evolution_process[$gen] = [
                    'generation' => $gen + 1,
                    'best_fitness' => $generation_data[$gen]['best_fitness'],
                    'optimal_count' => $generation_data[$gen]['optimal_count']
                ];
            }
        }
        
        // Tambahkan informasi tentang konflik yang terdeteksi
        $conflicts = $this->analyze_conflicts($best_schedule);
        
        // Return detailed process data dengan tambahan informasi
        return [
            'parameters' => [
                'population_size' => $this->population_size,
                'max_generations' => $this->max_generations,
                'crossover_rate' => $this->crossover_rate,
                'mutation_rate' => $this->mutation_rate,
                'elitism_count' => $this->elitism_count
            ],
            'data' => [
                'pending_count' => count($this->bookings),
                'fixed_count' => count($this->fixed_schedules),
                'approved_count' => count($this->approved_bookings),
                'pending_bookings' => $pending_bookings, // Tambahkan informasi booking
                'conflicts' => $conflicts['conflicts'], // Tambahkan informasi konflik
                'available_time_slots' => $this->available_time_slots // Tambahkan informasi slot waktu
            ],
            'generations' => $generation_data,
            'evolution_process' => $evolution_process, // Tambahkan proses evolusi
            'best_chromosome' => $best_chromosome,
            'best_fitness' => $best_fitness,
            'best_schedule' => $best_schedule, // Tambahkan jadwal terbaik
            'execution_time' => $execution_time,
            'convergence_rate' => $convergence_rate,
            'total_generations' => count($generation_data),
            'optimal_booking_count' => $optimal_count,
            'chromosome_length' => count($best_chromosome ?? []),
            'alternative_slots_used' => $this->count_alternative_slots($best_schedule) // Hitung penggunaan slot alternatif
        ];
    }
    
    // Metode untuk menganalisis konflik dalam jadwal
    public function analyze_conflicts($schedule) {
        $conflicts = [];
        $all_events = [];
        
        // Tambahkan jadwal tetap dan booking yang sudah disetujui
        foreach ($this->fixed_schedules as $fixed) {
            $all_events[] = [
                'id' => 'fixed_' . $fixed['id'],
                'title' => $fixed['title'],
                'date' => $fixed['date'],
                'start_time' => $fixed['start_time'],
                'end_time' => $fixed['end_time'],
                'type' => 'fixed'
            ];
        }
        
        foreach ($this->approved_bookings as $approved) {
            $all_events[] = [
                'id' => 'approved_' . $approved['id'],
                'title' => $approved['title'],
                'date' => $approved['date'],
                'start_time' => $approved['start_time'],
                'end_time' => $approved['end_time'],
                'type' => 'approved'
            ];
        }
        
        // Tambahkan jadwal yang dioptimasi
        foreach ($schedule as $booking) {
            $all_events[] = [
                'id' => 'optimized_' . $booking['id'],
                'title' => $booking['title'],
                'date' => $booking['date'],
                'start_time' => $booking['scheduled_start_time'],
                'end_time' => $booking['scheduled_end_time'],
                'type' => 'optimized',
                'is_alternative' => $booking['is_alternative'] ?? false
            ];
        }
        
        // Periksa konflik antara semua event
        for ($i = 0; $i < count($all_events); $i++) {
            for ($j = $i + 1; $j < count($all_events); $j++) {
                $event1 = $all_events[$i];
                $event2 = $all_events[$j];
                
                // Periksa apakah ada konflik waktu
                if ($event1['date'] == $event2['date'] && 
                    ($event1['start_time'] < $event2['end_time'] && $event1['end_time'] > $event2['start_time'])) {
                    
                    $conflicts[] = [
                        'event1' => [
                            'id' => $event1['id'],
                            'title' => $event1['title'],
                            'date' => $event1['date'],
                            'time' => $event1['start_time'] . ' - ' . $event1['end_time'],
                            'type' => $event1['type'],
                            'is_alternative' => $event1['is_alternative'] ?? false
                        ],
                        'event2' => [
                            'id' => $event2['id'],
                            'title' => $event2['title'],
                            'date' => $event2['date'],
                            'time' => $event2['start_time'] . ' - ' . $event2['end_time'],
                            'type' => $event2['type'],
                            'is_alternative' => $event2['is_alternative'] ?? false
                        ],
                        'recommendation' => $this->determine_recommendation($event1, $event2)
                    ];
                }
            }
        }
        
        return [
            'conflicts' => $conflicts,
            'total_conflicts' => count($conflicts)
        ];
    }
    
    // Metode untuk menentukan rekomendasi penyelesaian konflik
    private function determine_recommendation($event1, $event2) {
        // Prioritaskan jadwal tetap
        if ($event1['type'] == 'fixed' && $event2['type'] != 'fixed') {
            return [
                'event' => $event1,
                'reason' => 'Jadwal tetap'
            ];
        } else if ($event1['type'] != 'fixed' && $event2['type'] == 'fixed') {
            return [
                'event' => $event2,
                'reason' => 'Jadwal tetap'
            ];
        }
        
        // Prioritaskan jadwal yang sudah disetujui
        if ($event1['type'] == 'approved' && $event2['type'] == 'optimized') {
            return [
                'event' => $event1,
                'reason' => 'Jadwal sudah disetujui'
            ];
        } else if ($event1['type'] == 'optimized' && $event2['type'] == 'approved') {
            return [
                'event' => $event2,
                'reason' => 'Jadwal sudah disetujui'
            ];
        }
        
        // Prioritaskan jadwal yang menggunakan waktu asli
        if (isset($event1['is_alternative']) && isset($event2['is_alternative'])) {
            if (!$event1['is_alternative'] && $event2['is_alternative']) {
                return [
                    'event' => $event1,
                    'reason' => 'Waktu asli'
                ];
            } else if ($event1['is_alternative'] && !$event2['is_alternative']) {
                return [
                    'event' => $event2,
                    'reason' => 'Waktu asli'
                ];
            }
        }
        
        // Default
        return [
            'event' => $event1,
            'reason' => 'Default'
        ];
    }
    
    // Metode untuk menghitung jumlah slot alternatif yang digunakan
    private function count_alternative_slots($schedule) {
        $count = 0;
        foreach ($schedule as $booking) {
            if (isset($booking['is_alternative']) && $booking['is_alternative']) {
                $count++;
            }
        }
        return $count;
    }
    
    // Metode untuk menganalisis kromosom dengan slot waktu alternatif
    public function analyze_chromosome($chromosome) {
        $result = $this->calculate_fitness($chromosome);
        $schedule = $result['schedule'];
        $fitness = $result['fitness'];
        
        $scheduled_events = [];
        $conflicts = [];
        
        // Tambahkan jadwal tetap dan booking yang sudah disetujui
        foreach ($this->fixed_schedules as $fixed) {
            $scheduled_events[] = [
                'id' => 'fixed_' . $fixed['id'],
                'title' => $fixed['title'],
                'date' => $fixed['date'],
                'start_time' => $fixed['start_time'],
                'end_time' => $fixed['end_time'],
                'type' => 'fixed'
            ];
        }
        
        foreach ($this->approved_bookings as $approved) {
            $scheduled_events[] = [
                'id' => 'approved_' . $approved['id'],
                'title' => $approved['title'],
                'date' => $approved['date'],
                'start_time' => $approved['start_time'],
                'end_time' => $approved['end_time'],
                'type' => 'approved'
            ];
        }
        
        // Analisis konflik untuk setiap booking dalam jadwal
        foreach ($schedule as $booking) {
            $conflict_found = false;
            
            foreach ($scheduled_events as $event) {
                if ($booking['date'] == $event['date'] && 
                    ($booking['scheduled_start_time'] < $event['end_time'] && $booking['scheduled_end_time'] > $event['start_time'])) {
                    
                    $conflicts[] = [
                        'booking' => [
                            'id' => $booking['id'],
                            'title' => $booking['title'],
                            'date' => $booking['date'],
                            'time' => $booking['scheduled_start_time'] . ' - ' . $booking['scheduled_end_time'],
                            'is_alternative' => $booking['is_alternative'] ?? false
                        ],
                        'conflict_with' => [
                            'id' => $event['id'],
                            'title' => $event['title'],
                            'date' => $event['date'],
                            'time' => $event['start_time'] . ' - ' . $event['end_time'],
                            'type' => $event['type']
                        ]
                    ];
                    
                    $conflict_found = true;
                    break;
                }
            }
            
            if (!$conflict_found) {
                $scheduled_events[] = [
                    'id' => 'pending_' . $booking['id'],
                    'title' => $booking['title'],
                    'date' => $booking['date'],
                    'start_time' => $booking['scheduled_start_time'],
                    'end_time' => $booking['scheduled_end_time'],
                    'type' => 'pending',
                    'is_alternative' => $booking['is_alternative'] ?? false
                ];
            }
        }
        
        return [
            'scheduled_bookings' => $schedule,
            'conflicts' => $conflicts,
            'total_scheduled' => count($schedule),
            'total_conflicts' => count($conflicts),
            'fitness' => $fitness,
            'alternative_slots_used' => $this->count_alternative_slots($schedule)
        ];
    }
    
    // Metode untuk mendapatkan visualisasi kromosom dengan slot waktu alternatif
    public function get_chromosome_visualization($chromosome) {
        $visualization = [];
        
        for ($i = 0; $i < count($this->bookings); $i++) {
            $include_index = $i * 2;
            $slot_index = $i * 2 + 1;
            $booking = $this->bookings[$i];
            $include_gene = $chromosome[$include_index];
            $slot_gene = $chromosome[$slot_index];
            
            // Tentukan slot waktu yang digunakan
            $time_slot_id = $slot_gene % count($this->available_time_slots);
            $alternative_start_time = $this->available_time_slots[$time_slot_id];
            $original_duration = $this->calculateDuration($booking['start_time'], $booking['end_time']);
            $alternative_end_time = $this->addMinutesToTime($alternative_start_time, $original_duration);
            
            $visualization[] = [
                'genes' => [
                    'include' => $include_gene,
                    'time_slot' => $slot_gene
                ],
                'booking' => [
                    'id' => $booking['id'],
                    'title' => $booking['title'],
                    'date' => $booking['date'],
                    'original_time' => $booking['start_time'] . ' - ' . $booking['end_time'],
                    'alternative_time' => $alternative_start_time . ' - ' . $alternative_end_time,
                    'is_included' => ($include_gene == 1),
                    'is_alternative' => ($time_slot_id > 0),
                    'time_slot_id' => $time_slot_id,
                    'activity_type' => $booking['activity_type'] ?? 'Unknown'
                ]
            ];
        }
        
        return $visualization;
    }
    
    // Metode untuk mendapatkan rekomendasi jadwal alternatif untuk booking yang konflik
    public function get_alternative_recommendations($booking_id) {
        // Temukan booking dengan ID yang diberikan
        $target_booking = null;
        foreach ($this->bookings as $booking) {
            if ($booking['id'] == $booking_id) {
                $target_booking = $booking;
                break;
            }
        }
        
        if (!$target_booking) {
            return [
                'success' => false,
                'message' => 'Booking tidak ditemukan'
            ];
        }
        
        // Dapatkan durasi booking
        $duration = $this->calculateDuration($target_booking['start_time'], $target_booking['end_time']);
        
        // Dapatkan semua jadwal yang sudah ada
        $existing_schedules = [];
        
        // Tambahkan jadwal tetap
        foreach ($this->fixed_schedules as $schedule) {
            if ($schedule['date'] == $target_booking['date']) {
                $existing_schedules[] = [
                    'start_time' => $schedule['start_time'],
                    'end_time' => $schedule['end_time']
                ];
            }
        }
        
        // Tambahkan booking yang sudah disetujui
        foreach ($this->approved_bookings as $booking) {
            if ($booking['date'] == $target_booking['date']) {
                $existing_schedules[] = [
                    'start_time' => $booking['start_time'],
                    'end_time' => $booking['end_time']
                ];
            }
        }
        
        // Cari slot waktu alternatif yang tersedia
        $available_alternatives = [];
        
        foreach ($this->available_time_slots as $slot_id => $start_time) {
            // Lewati slot waktu asli
            if ($start_time == $target_booking['start_time']) {
                continue;
            }
            
            $end_time = $this->addMinutesToTime($start_time, $duration);
            
            // Periksa apakah slot ini tersedia (tidak konflik dengan jadwal yang sudah ada)
            $has_conflict = false;
            
            foreach ($existing_schedules as $schedule) {
                if (($start_time < $schedule['end_time'] && $end_time > $schedule['start_time'])) {
                    $has_conflict = true;
                    break;
                }
            }
            
            if (!$has_conflict) {
                $available_alternatives[] = [
                    'slot_id' => $slot_id,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'duration' => $duration,
                    'time_difference' => $this->calculateTimeDifference($target_booking['start_time'], $start_time)
                ];
            }
        }
        
        // Urutkan alternatif berdasarkan perbedaan waktu dengan waktu asli (yang paling dekat dulu)
        usort($available_alternatives, function($a, $b) {
            return $a['time_difference'] - $b['time_difference'];
        });
        
        return [
            'success' => true,
            'booking' => $target_booking,
            'alternative_slots' => $available_alternatives,
            'total_alternatives' => count($available_alternatives)
        ];
    }
    
    // Metode untuk menghitung perbedaan waktu dalam menit
    private function calculateTimeDifference($time1, $time2) {
        $timestamp1 = strtotime($time1);
        $timestamp2 = strtotime($time2);
        return abs($timestamp1 - $timestamp2) / 60; // Konversi detik ke menit
    }
    
    // Metode untuk mengoptimasi jadwal dengan preferensi slot waktu
    public function optimize_with_preferred_slots($preferred_slots = []) {
        // preferred_slots format: [booking_id => slot_id, ...]
        
        $population = $this->initialize_population();
        $best_chromosome = null;
        $best_fitness = -1;
        $best_schedule = [];
        
        // Modifikasi populasi awal untuk menyertakan preferensi slot waktu
        foreach ($population as &$chromosome) {
            foreach ($preferred_slots as $booking_id => $preferred_slot_id) {
                // Temukan indeks booking dalam array bookings
                $booking_index = -1;
                for ($i = 0; $i < count($this->bookings); $i++) {
                    if ($this->bookings[$i]['id'] == $booking_id) {
                        $booking_index = $i;
                        break;
                    }
                }
                
                if ($booking_index >= 0) {
                    $include_index = $booking_index * 2;
                    $slot_index = $booking_index * 2 + 1;
                    
                    // Set gen include menjadi 1 (masukkan booking)
                    $chromosome[$include_index] = 1;
                    
                    // Set gen slot waktu ke slot yang dipreferensikan
                    $chromosome[$slot_index] = $preferred_slot_id;
                }
            }
        }
        
        // Lanjutkan dengan algoritma genetika seperti biasa
        for ($generation = 0; $generation < $this->max_generations; $generation++) {
            // Calculate fitness for each chromosome
            $fitness_scores = [];
            $schedules = [];
            
            foreach ($population as $chromosome) {
                $result = $this->calculate_fitness_with_preferences($chromosome, $preferred_slots);
                $fitness_scores[] = $result['fitness'];
                $schedules[] = $result['schedule'];
                
                if ($result['fitness'] > $best_fitness) {
                    $best_fitness = $result['fitness'];
                    $best_chromosome = $chromosome;
                    $best_schedule = $result['schedule'];
                }
            }
            
            // Create new population
            $new_population = [];
            
            // Elitism - keep best chromosomes
            $combined = array_combine(range(0, count($population) - 1), $fitness_scores);
            arsort($combined);
            $elite_indices = array_slice(array_keys($combined), 0, $this->elitism_count, true);
            
            foreach ($elite_indices as $index) {
                $new_population[] = $population[$index];
            }
            
            // Create rest of the population through selection, crossover, and mutation
            while (count($new_population) < $this->population_size) {
                $parents = $this->select_parents($population, $fitness_scores);
                $children = $this->crossover($parents[0], $parents[1]);
                
                $children[0] = $this->mutate_with_preferences($children[0], $preferred_slots);
                $children[1] = $this->mutate_with_preferences($children[1], $preferred_slots);
                
                $new_population[] = $children[0];
                
                if (count($new_population) < $this->population_size) {
                    $new_population[] = $children[1];
                }
            }
            
            $population = $new_population;
        }
        
        // Return the best solution
        return $best_schedule;
    }
    
    // Calculate fitness with preference for specific time slots
    private function calculate_fitness_with_preferences($chromosome, $preferred_slots) {
        $result = $this->calculate_fitness($chromosome);
        $fitness = $result['fitness'];
        $schedule = $result['schedule'];
        
        // Berikan bonus fitness untuk booking yang menggunakan slot waktu yang dipreferensikan
        foreach ($schedule as $booking) {
            if (isset($preferred_slots[$booking['id']])) {
                $preferred_slot_id = $preferred_slots[$booking['id']];
                $booking_slot_id = -1;
                
                // Temukan indeks booking dalam array bookings
                $booking_index = -1;
                for ($i = 0; $i < count($this->bookings); $i++) {
                    if ($this->bookings[$i]['id'] == $booking['id']) {
                        $booking_index = $i;
                        break;
                    }
                }
                
                if ($booking_index >= 0) {
                    $slot_index = $booking_index * 2 + 1;
                    $booking_slot_id = $chromosome[$slot_index] % count($this->available_time_slots);
                }
                
                if ($booking_slot_id == $preferred_slot_id) {
                    $fitness += 0.5; // Bonus untuk menggunakan slot waktu yang dipreferensikan
                }
            }
        }
        
        return [
            'fitness' => $fitness,
            'schedule' => $schedule
        ];
    }
    
    // Mutate chromosome while preserving preferred slots
    private function mutate_with_preferences($chromosome, $preferred_slots) {
        for ($i = 0; $i < count($chromosome); $i++) {
            if (rand(0, 100) / 100 < $this->mutation_rate) {
                // Untuk gen include/exclude (indeks genap)
                if ($i % 2 == 0) {
                    $booking_index = $i / 2;
                    $booking_id = $this->bookings[$booking_index]['id'] ?? null;
                    
                    // Jika booking ini memiliki preferensi slot, jangan ubah gen include
                    if ($booking_id && isset($preferred_slots[$booking_id])) {
                        continue;
                    }
                    
                    $chromosome[$i] = 1 - $chromosome[$i]; // Flip 0 jadi 1, atau 1 jadi 0
                } else {
                    // Untuk gen slot waktu (indeks ganjil)
                    $booking_index = ($i - 1) / 2;
                    $booking_id = $this->bookings[$booking_index]['id'] ?? null;
                    
                    // Jika booking ini memiliki preferensi slot, jangan ubah gen slot waktu
                    if ($booking_id && isset($preferred_slots[$booking_id])) {
                        continue;
                    }
                    
                    $chromosome[$i] = rand(0, count($this->available_time_slots) - 1);
                }
            }
        }
        
        return $chromosome;
    }
    
    // Metode untuk menyimpan hasil optimasi ke database
    public function save_optimized_schedule($schedule) {
        try {
            // Periksa apakah kolom is_alternative sudah ada
            $column_exists = false;
            try {
                $check_column = $this->conn->query("SHOW COLUMNS FROM bookings LIKE 'is_alternative'");
                $column_exists = ($check_column->rowCount() > 0);
            } catch (PDOException $e) {
                // Kolom tidak ada
            }
            
            // Tambahkan kolom jika belum ada
            if (!$column_exists) {
                try {
                    $this->conn->exec("ALTER TABLE bookings ADD COLUMN is_alternative TINYINT(1) DEFAULT 0");
                } catch (PDOException $e) {
                    return [
                        "success" => false,
                        "message" => "Error adding column: " . $e->getMessage()
                    ];
                }
            }
            
            // Tambahkan kolom scheduled_start_time dan scheduled_end_time jika belum ada
            $columns_to_check = ['scheduled_start_time', 'scheduled_end_time'];
            foreach ($columns_to_check as $column) {
                try {
                    $check_column = $this->conn->query("SHOW COLUMNS FROM bookings LIKE '$column'");
                    if ($check_column->rowCount() == 0) {
                        $this->conn->exec("ALTER TABLE bookings ADD COLUMN $column TIME NULL");
                    }
                } catch (PDOException $e) {
                    return [
                        "success" => false,
                        "message" => "Error checking/adding column $column: " . $e->getMessage()
                    ];
                }
            }
            
            // Mulai transaksi
            $this->conn->beginTransaction();
            
            $approved_count = 0;
            
            foreach ($schedule as $booking) {
                // Persiapkan query SQL
                $sql = "UPDATE bookings SET status = :status";
                
                // Tambahkan kolom scheduled_time jika ada
                if (isset($booking["scheduled_start_time"])) {
                    $sql .= ", scheduled_start_time = :start_time";
                }
                if (isset($booking["scheduled_end_time"])) {
                    $sql .= ", scheduled_end_time = :end_time";
                }
                
                // Tambahkan kolom is_alternative
                $sql .= ", is_alternative = :is_alternative WHERE id = :id";
                
                $stmt = $this->conn->prepare($sql);
                
                $status = "approved";
                $stmt->bindParam(":status", $status);
                
                if (isset($booking["scheduled_start_time"])) {
                    $stmt->bindParam(":start_time", $booking["scheduled_start_time"]);
                }
                if (isset($booking["scheduled_end_time"])) {
                    $stmt->bindParam(":end_time", $booking["scheduled_end_time"]);
                }
                
                // Bind parameter is_alternative
                $is_alternative = isset($booking["is_alternative"]) ? (int)$booking["is_alternative"] : 0;
                $stmt->bindParam(":is_alternative", $is_alternative, PDO::PARAM_INT);
                
                $stmt->bindParam(":id", $booking["id"]);
                
                if ($stmt->execute()) {
                    $approved_count++;
                }
            }
            
            // Commit transaksi
            $this->conn->commit();
            
            return [
                "success" => true,
                "message" => "Jadwal berhasil disimpan",
                "approved_count" => $approved_count
            ];
        } catch (PDOException $e) {
            // Rollback transaksi jika terjadi error
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            
            return [
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ];
        }
    }
    
    // Metode untuk mendapatkan statistik penggunaan slot waktu
    public function get_time_slot_statistics() {
        $slot_usage = array_fill(0, count($this->available_time_slots), 0);
        
        // Hitung penggunaan slot waktu untuk booking yang sudah disetujui
        foreach ($this->approved_bookings as $booking) {
            $start_time = $booking['start_time'];
            
            // Temukan slot waktu yang sesuai
            $slot_id = array_search($start_time, $this->available_time_slots);
            
            if ($slot_id !== false) {
                $slot_usage[$slot_id]++;
            }
        }
        
        // Buat statistik untuk setiap slot waktu
        $statistics = [];
        foreach ($this->available_time_slots as $slot_id => $start_time) {
            $end_time = $this->addMinutesToTime($start_time, 30); // Asumsi slot 30 menit
            
            $statistics[] = [
                'slot_id' => $slot_id,
                'time_range' => $start_time . ' - ' . $end_time,
                'usage_count' => $slot_usage[$slot_id],
                'usage_percentage' => count($this->approved_bookings) > 0 ? 
                                     round(($slot_usage[$slot_id] / count($this->approved_bookings)) * 100, 2) : 0
            ];
        }
        
        return $statistics;
    }
    
    // Metode untuk mendapatkan rekomendasi jadwal berdasarkan pola penggunaan historis
    public function get_schedule_recommendations() {
        // Dapatkan statistik penggunaan slot waktu
        $slot_statistics = $this->get_time_slot_statistics();
        
        // Urutkan slot berdasarkan penggunaan (dari yang paling sedikit digunakan)
        usort($slot_statistics, function($a, $b) {
            return $a['usage_count'] - $b['usage_count'];
        });
        
        // Buat rekomendasi untuk setiap booking yang menunggu
        $recommendations = [];
        
        foreach ($this->bookings as $booking) {
            $duration = $this->calculateDuration($booking['start_time'], $booking['end_time']);
            $recommended_slots = [];
            
            // Cek ketersediaan setiap slot waktu
            foreach ($slot_statistics as $slot) {
                $slot_id = $slot['slot_id'];
                $start_time = $this->available_time_slots[$slot_id];
                $end_time = $this->addMinutesToTime($start_time, $duration);
                
                // Periksa konflik dengan jadwal yang sudah ada
                $has_conflict = false;
                
                foreach ($this->fixed_schedules as $fixed) {
                    if ($booking['date'] == $fixed['date'] && 
                        ($start_time < $fixed['end_time'] && $end_time > $fixed['start_time'])) {
                        $has_conflict = true;
                        break;
                    }
                }
                
                if (!$has_conflict) {
                    foreach ($this->approved_bookings as $approved) {
                        if ($booking['date'] == $approved['date'] && 
                            ($start_time < $approved['end_time'] && $end_time > $approved['start_time'])) {
                            $has_conflict = true;
                            break;
                        }
                    }
                }
                
                if (!$has_conflict) {
                    $recommended_slots[] = [
                        'slot_id' => $slot_id,
                        'start_time' => $start_time,
                        'end_time' => $end_time,
                        'usage_count' => $slot['usage_count'],
                        'time_difference' => $this->calculateTimeDifference($booking['start_time'], $start_time)
                    ];
                }
                
                // Batasi jumlah rekomendasi per booking
                if (count($recommended_slots) >= 5) {
                    break;
                }
            }
            
            $recommendations[$booking['id']] = [
                'booking' => $booking,
                'recommended_slots' => $recommended_slots,
                'total_recommendations' => count($recommended_slots)
            ];
        }
        
        return $recommendations;
    }
}
?>
