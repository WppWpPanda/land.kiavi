<?php
/**
 * FormFieldsManager - менеджер полей формы
 */
class FormFieldsManager {
    /**
     * @var string Путь к директории с полями
     */
    private $fieldsDir;

    /**
     * @var array Зарегистрированные типы полей
     */
    private $registeredFields = [];

    /**
     * @var FormFieldsManager Единственный экземпляр класса
     */
    private static $instance;

    /**
     * Конструктор класса
     */
    private function __construct() {
        $this->fieldsDir = trailingslashit(__DIR__) . 'fields';
        $this->initHooks();
    }

    /**
     * Инициализация хуков
     */
    private function initHooks(): void {
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontendAssets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
    }

    /**
     * Получить экземпляр класса (Singleton)
     *
     * @return FormFieldsManager
     */
    public static function getInstance(): FormFieldsManager {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Инициализация менеджера полей
     */
    public function init(): void {
        $this->loadFields();
    }

    /**
     * Загружает все поля из директории fields
     */
    private function loadFields(): void {
        if (!is_dir($this->fieldsDir)) {
            return;
        }

        $fieldTypes = scandir($this->fieldsDir);

        foreach ($fieldTypes as $fieldType) {
            if ($fieldType === '.' || $fieldType === '..') {
                continue;
            }

            $fieldPath = trailingslashit($this->fieldsDir) . $fieldType . '/field-' . $fieldType . '.php';

            if (file_exists($fieldPath)) {
                require_once $fieldPath;
                $this->registeredFields[$fieldType] = [
                    'path' => $fieldPath,
                    'class' => 'Field_' . str_replace('-', '_', ucfirst($fieldType))
                ];
            }
        }
    }

    /**
     * Подключает CSS и JS для фронтенда
     */
    public function enqueueFrontendAssets(): void {
        $this->enqueueAssets(false);
    }

    /**
     * Подключает CSS и JS для админки
     */
    public function enqueueAdminAssets(): void {
        $this->enqueueAssets(true);
    }

    /**
     * Подключает CSS и JS для всех полей
     * @param bool $isAdmin Для админки или фронтенда
     */
    private function enqueueAssets(bool $isAdmin): void {
        foreach ($this->registeredFields as $fieldType => $fieldData) {
            $cssPath = trailingslashit($this->fieldsDir) . $fieldType . '/css.css';
            $jsPath = trailingslashit($this->fieldsDir) . $fieldType . '/js.js';

            if (file_exists($cssPath)) {
                $handle = 'field-' . sanitize_key($fieldType) . '-css';
                $src = $this->getAssetUrl($cssPath);

                wp_enqueue_style(
                    $handle,
                    $src,
                    [],
                    filemtime($cssPath)
                );
            }

            if (file_exists($jsPath)) {
                $handle = 'field-' . sanitize_key($fieldType) . '-js';
                $src = $this->getAssetUrl($jsPath);

                wp_enqueue_script(
                    $handle,
                    $src,
                    ['jquery'],
                    filemtime($jsPath),
                    true
                );
            }
        }
    }

    /**
     * Преобразует локальный путь в URL
     * @param string $path Локальный путь к файлу
     * @return string URL файла
     */
    private function getAssetUrl(string $path): string {
        // Если файл находится внутри темы
        $themePath = get_stylesheet_directory();
        if (strpos($path, $themePath) === 0) {
            return str_replace($themePath, get_stylesheet_directory_uri(), $path);
        }

        // Если файл находится внутри плагина
        $pluginPath = plugin_dir_path(__FILE__);
        if (strpos($path, $pluginPath) === 0) {
            return plugins_url(str_replace($pluginPath, '', $path), __FILE__);
        }

        // По умолчанию используем content_url
        return str_replace(WP_CONTENT_DIR, content_url(), $path);
    }

    /**
     * API: Добавляет новое поле
     *
     * @param string $fieldType Тип поля (директория)
     * @param string $className Имя класса поля
     * @param string $filePath Полный путь к файлу поля
     */
    public function addField(string $fieldType, string $className, string $filePath): void {
        if (!file_exists($filePath)) {
            throw new Exception("Field file does not exist: " . $filePath);
        }

        require_once $filePath;
        $this->registeredFields[$fieldType] = [
            'path' => $filePath,
            'class' => $className
        ];
    }

    /**
     * API: Рендерит поле
     *
     * @param string $fieldType Тип поля
     * @param array $args Аргументы поля
     * @return string HTML поля
     */
    public function renderField(string $fieldType, array $args = []): string {
        if (!isset($this->registeredFields[$fieldType])) {
            throw new Exception("Field type not registered: " . $fieldType);
        }

        $className = $this->registeredFields[$fieldType]['class'];

        if (!class_exists($className)) {
            throw new Exception("Field class does not exist: " . $className);
        }

        $field = new $className($args);
        return $field->render();
    }

    /**
     * API: Получить список зарегистрированных полей
     *
     * @return array
     */
    public function getRegisteredFields(): array {
        return array_keys($this->registeredFields);
    }
}