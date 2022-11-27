/**
 * Load ky.
 */
import { default as ky, Options } from 'ky-universal';

/**
 * Load configurations.
 */
import config from './config.json';

/**
 * Create a abort controller.
 */
const abortController = new AbortController();
const { signal: abortSignal } = abortController;

/**
 * Create a new ky instance with default options.
 */
const kyOptions: Options = {
    prefixUrl: config.baseUrl,
    timeout: config.timeout,
    throwHttpErrors: config.throwHttpErrors,
    signal: abortSignal
};
const kyInstance = ky.extend(kyOptions);

/**
 * API client.
 */
export namespace Client {
    /**
     * HTTP method.
     */
    export type HttpMethod = 'get'|'post'|'put'|'patch'|'delete'|'head';

    /**
     * Send HTTP request.
     *
     * @param httpMethod {HttpMethod} HTTP method to request with.
     * @param endpoint {string} URL endpoint to request on.
     * @param data? {object} Data object to request with. Should not use with GET, DELETE, HEAD method.
     * @return {Promise<string>} HTTP response string.
     */
    export const request = async (httpMethod: HttpMethod, endpoint: string, data?: object): Promise<string> => {
        return kyInstance(endpoint, {
            method: httpMethod,
            json: data
        }).text();
    };

    /**
     * Abort current HTTP request.
     * It will make the HTTP request to throw HTTPError.
     *
     * @return {void}
     */
    export const abort = (): void => {
        abortController.abort();
    };

    /**
     * Set access token.
     *
     * @param accessToken {string} Bearer access token.
     * @return {void}
     */
    export const setAccessToken = (accessToken: string): void => {
        kyOptions.headers = {
            'Authorization': `Bearer ${accessToken}`
        };
    };
}