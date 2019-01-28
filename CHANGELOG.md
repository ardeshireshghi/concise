* 0.3.3
* 0.4.0

- Add `headers` to request using `getallheaders` or the polyfill
- Update tests and add tests for `Concise\Http\Request`

* 0.3.3

- Move HTTP response `send` logic to responseAdapter to make it internal
- Remove `send` from tests, example app and README example

* 0.3.2

- Add body parsing logic to request adapter (raw, json, form-data and url-encoded)
- Update App example app and README example

* 0.3.1

- Improve the combined wrapped middleware logic (middleware reducer)


* 0.3.0

- Introduce route handler request array which replaces route params. It includes:
  Route params (e.g id in api/:id), Body in post/put/patch requests, Query in get requests, Method (e.g POST, GET)

- Introduce Http request Adapter which creates the request array/object based on the http variables
- Update and improve example web-api with changes in request changes as well as adding a auth middleware
- Update and improve unit tests and Spy TestUtil


* 0.2.1

- Fix the example app
- Update documentation with adding a logger middleware
- Introduce CHANGELOG


* 0.2.0

- Routing and route matching logic
- Response chain (header, send and response functions)
- App and route handler middleware logic
- Session handling middleware
- Middleware creator/factory
