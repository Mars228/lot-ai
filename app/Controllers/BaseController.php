<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    /**
     * Wspólne helpery dla wszystkich kontrolerów
     */
    protected $helpers = ['url', 'form', 'date_helper', 'lottery_helper', 'system_helper'];

    /**
     * Wspólne dane dla widoków
     */
    protected array $viewData = [];

    /**
     * Wspólne modele (lazy loading)
     */
    protected $gameModel;
    protected $settingsModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);

        // Przygotowanie wspólnych danych dla widoków (bez modeli)
        $this->prepareBaseViewData();
    }

    /**
     * Przygotowuje podstawowe dane dla widoków (bez dostępu do bazy)
     */
    protected function prepareBaseViewData(): void
    {
        $this->viewData = [
            'pageTitle' => 'Analizator Gier Liczbowych',
            'currentController' => $this->request->getUri()->getSegment(1) ?: 'home',
            'games' => [], // Zostanie załadowane lazy loading
            'appVersion' => '1.0', // Domyślna wartość
            'systemInfo' => getSystemInfo(),
            'dbStatus' => getDatabaseStatus(),
            'apiStatus' => getApiStatus()
        ];
    }

    /**
     * Lazy loading dla GameModel
     */
    protected function getGameModel()
    {
        if ($this->gameModel === null) {
            $this->gameModel = model('GameModel');
        }
        return $this->gameModel;
    }

    /**
     * Lazy loading dla SettingsModel
     */
    protected function getSettingsModel()
    {
        if ($this->settingsModel === null) {
            $this->settingsModel = model('SettingsModel');
        }
        return $this->settingsModel;
    }

    /**
     * Ładuje pełne dane dla widoków (wywołaj w każdym kontrolerze przed renderowaniem)
     */
    protected function loadViewData(): void
    {
        try {
            $this->viewData['games'] = $this->getGameModel()->where('is_active', 1)->findAll();
            $this->viewData['appVersion'] = $this->getSettingsModel()->getValue('database_version', '1.0');
        } catch (\Throwable $e) {
            // W przypadku błędu bazy danych, użyj wartości domyślnych
            log_message('error', 'Błąd ładowania danych w BaseController: ' . $e->getMessage());
            $this->viewData['games'] = [];
            $this->viewData['appVersion'] = '1.0';
        }
    }

    /**
     * Pomocnicza metoda do zwracania odpowiedzi JSON
     */
    protected function jsonResponse(array $data, int $statusCode = 200)
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON($data);
    }

    /**
     * Pomocnicza metoda do wyświetlania komunikatów
     */
    protected function setMessage(string $message, string $type = 'info'): void
    {
        session()->setFlashdata('message', $message);
        session()->setFlashdata('messageType', $type);
    }

    /**
     * Walidacja czy gra istnieje
     */
    protected function validateGameExists(int $gameId): bool
    {
        try {
            return $this->getGameModel()->find($gameId) !== null;
        } catch (\Throwable $e) {
            log_message('error', 'Błąd walidacji gry: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Pomocnicza metoda do renderowania widoków z załadowanymi danymi
     */
    protected function renderView(string $view, array $additionalData = [])
    {
        $this->loadViewData();
        $data = array_merge($this->viewData, $additionalData);
        return view($view, $data);
    }
}