
# PHP Multithread Bundle

A Symfony bundle for running multiple PHP methods in parallel, leveraging process control (pcntl) for true multithreading. This allows you to execute service methods concurrently, collect their results, and monitor resource usage, all integrated with Symfony’s profiler.

## Features

- Run multiple service methods in parallel threads
- Collect results and resource usage for each thread
- Symfony Profiler integration for debugging and performance analysis
- Simple API for thread creation and execution

---

## Installation

1. **Add the VCS to your composer file:**
   ```
   "repositories": [
	   {
		   "type": "vcs",
		   "url": "https://github.com/tbessenreither/php-multithread"
	   }
   ]
   ```

2. **Install the package via composer:**
   ```
   composer require tbessenreither/php-multithread
   ```

3. **Register the bundle in `config/bundles.php`:**
   ```php
   return [
	   // ...
	   Tbessenreither\PhpMultithread\Bundle\PhpMultithreadBundle::class => ['all' => true],
   ];
   ```

---

## Usage


### 1. Inject the Multithread Service

Inject `MultithreadService` into your command or service:

```php
public function __construct(
	private MultithreadService $multithreadService,
) {}
```

### 2. Create Thread DTOs

Prepare an array of `ThreadDto` objects, each representing a method call to run in parallel:

```php
use Tbessenreither\PhpMultithread\Dto\ThreadDto;

$threads = [
	new ThreadDto(
		class: TestService::class,
		method: 'doSomethingSlow',
		parameters: [],
		timeout: 3,
	),
	new ThreadDto(
		class: TestService::class,
		method: 'doSomething',
		parameters: [],
	),
	// ...add more threads as needed
];
```

> **Important:** The service class you reference in a `ThreadDto` must be tagged for runtime injection using the `AsTaggedItem` attribute. This is enforced at runtime:
>
> ```php
> use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
> use Tbessenreither\PhpMultithread\Service\RuntimeAutowireService;
>
> #[AsTaggedItem(RuntimeAutowireService::AUTOWIRE_TAG)]
> class TestService {
>     // ...
> }
> ```

### 3. Run Threads

Execute all threads in parallel and collect the results:

```php
$results = $this->multithreadService->runThreads($threads);
```

### 4. Access Results



Each result is a `ResponseDto` containing the output and error (if any):


```php
foreach ($results as $response) {
	if ($response->hasError()) {
		// error handling
		$error = $response->getError();
		// ...handle error
	} else {
		// get result
		$result = $response->getResult(MyResultType::class); // or omit type for mixed
		// ...use result
	}
}
```

Additionally, the `ResponseDto` is also available directly in each `ThreadDto` after execution, as it is updated by the service.

Resource usage information is available via `getResourceUsageDto()`, but this is primarily intended for profiler integration and advanced diagnostics.

#### Type-Safe Results with `getResult()`

The `ResponseDto::getResult()` method supports type checking for safer and more expressive code. Simply pass the expected class name as a string, and an exception will be thrown if the type does not match. If you do not specify a type, the result will be returned as-is (mixed).

---

## Symfony Profiler Integration

When running your command with the `--profile` option, you’ll see a dedicated "PHP Multithread" section in the Symfony Profiler. This displays:

- Executed commands and their metrics
- Thread errors and context
- Resource usage per thread

![Symfony Profiler Multithread Screenshot](documentation/examples/profiler_screenshot.png)

---

---



## Requirements

- PHP ^8.4
- symfony/config ^7.4
- symfony/dependency-injection ^7.4
- symfony/http-kernel ^7.4
- symfony/process ^8.0
- symfony/uid ^8.0

The `pcntl` extension is recommended for best performance. If `pcntl` is not available, the bundle will fall back to the command runner, which is less performant.

---

## License

MIT

---


---

## Project Structure & Architecture

The bundle is organized to separate concerns and provide extensibility:

- **src/**: Main source code
	- **Bundle/**: Bundle registration and DI configuration
	- **Command/**: Symfony console commands for multithread execution
	- **DataCollector/**: Symfony profiler integration
	- **Dto/**: Data transfer objects for threads, responses, and resource usage
	- **Interface/**: Interfaces for thread runners
	- **Service/**: Core services for thread management and execution
		- **Runners/**: Different runner implementations (e.g., PcntlRunner, CommandRunner)
	- **Templates/**: Twig templates for profiler UI
	- **Trait/**: Shared traits
- **documentation/**: Example usage and screenshots
- **tests/**: Test suite and bootstrap
- **composer.json**: Dependency and requirement definitions

