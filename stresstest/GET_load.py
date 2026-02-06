import requests

import time

import random

from concurrent.futures import ThreadPoolExecutor



def api_call(_):

    response = requests.get("http://localhost/index.php")

    # You may add checks here to verify the response

    elapsed_time = response.elapsed.total_seconds() 

    return [response.status_code, elapsed_time]



# Number of threads to use for the requests

num_threads = 400





# Create a ThreadPoolExecutor with the given number of threads

with ThreadPoolExecutor(max_workers=num_threads) as executor:

    # Use map to execute the api_call function for each thread

    results = list(executor.map(api_call, range(num_threads)))

    random_number = random.randint(5000, 10000)

    time.sleep(random_number/1000)



# Print the results of the API calls

statusCount = {}

avgSuccess = 0

for result in results:

    if result[0] in statusCount:

        statusCount[result[0]] +=1

    else:

        statusCount[result[0]] = 1

    print(f"Response status code: {result[0]} and time elapsed: {result[1]}")



print('-----------------------------')

print(statusCount)