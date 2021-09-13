DIR=$(PWD)
concurrency="5"
scale_list="1 2 4 8 16 32 64 128 256 512 1024"
workload_list="10 20 40 80"

container_nginx=$(docker run --rm -d -p 81:80/tcp benchmark:nginx)
container_hybrid=$(docker run --rm -d -p 82:80/tcp benchmark:hybrid)
container_ppm=$(docker run --rm -d -p 83:80/tcp benchmark:ppm)

for workload in $workload_list; do
    mkdir -p $DIR/result/workload/nginx/$workload
    cd $DIR/result/workload/nginx/$workload

    echo "Start nginx $workload/$concurrency"
    ab -q -k -n $workload -c $concurrency -g basic.txt http://localhost:81/basic > basic_result.txt 2>&1
    ab -q -k -n $workload -c $concurrency -g hash_snefru.txt http://localhost:81/hash-snefru > hash_snefru_result.txt 2>&1
    ab -q -k -n $workload -c $concurrency -g hash_ghost.txt http://localhost:81/hash-ghost > hash_ghost_result.txt 2>&1
    # ab -q -k -n $workload -c $concurrency -g sleep.txt http://localhost:81/sleep > sleep_result.txt 2>&1
    ab -q -k -n $workload -c $concurrency -g stream.txt http://localhost:81/stream > stream_result.txt 2>&1
    ab -q -k -n $workload -c $concurrency -g random.txt http://localhost:81/random > random_result.txt 2>&1
    ab -q -k -n $workload -c $concurrency -g big_stream.txt http://localhost:81/big-stream > big_stream_result.txt 2>&1
done

for workload in $workload_list; do
    mkdir -p $DIR/result/workload/hybrid/$workload
    cd $DIR/result/workload/hybrid/$workload

    echo "Start hybrid $workload/$concurrency"
    ab -q -k -n $workload -c $concurrency -g basic.txt http://localhost:82/basic > basic_result.txt 2>&1
    ab -q -k -n $workload -c $concurrency -g hash_snefru.txt http://localhost:82/hash-snefru > hash_snefru_result.txt 2>&1
    ab -q -k -n $workload -c $concurrency -g hash_ghost.txt http://localhost:82/hash-ghost > hash_ghost_result.txt 2>&1
    # ab -q -k -n $workload -c $concurrency -g sleep.txt http://localhost:82/sleep > sleep_result.txt 2>&1
    ab -q -k -n $workload -c $concurrency -g stream.txt http://localhost:82/stream > stream_result.txt 2>&1
    ab -q -k -n $workload -c $concurrency -g random.txt http://localhost:82/random > random_result.txt 2>&1
    ab -q -k -n $workload -c $concurrency -g big_stream.txt http://localhost:82/big-stream > big_stream_result.txt 2>&1
done

for workload in $workload_list; do
    mkdir -p $DIR/result/workload/ppm/$workload
    cd $DIR/result/workload/ppm/$workload

    echo "Start ppm $workload/$concurrency"
    ab -q -k -n $workload -c $concurrency -g basic.txt http://localhost:83/basic > basic_result.txt 2>&1
    ab -q -k -n $workload -c $concurrency -g hash_snefru.txt http://localhost:83/hash-snefru > hash_snefru_result.txt 2>&1
    ab -q -k -n $workload -c $concurrency -g hash_ghost.txt http://localhost:83/hash-ghost > hash_ghost_result.txt 2>&1
    # ab -q -k -n $workload -c $concurrency -g sleep.txt http://localhost:83/sleep > sleep_result.txt 2>&1
    ab -q -k -n $workload -c $concurrency -g stream.txt http://localhost:83/stream > stream_result.txt 2>&1
    ab -q -k -n $workload -c $concurrency -g random.txt http://localhost:83/random > random_result.txt 2>&1
    ab -q -k -n $workload -c $concurrency -g big_stream.txt http://localhost:83/big-stream > big_stream_result.txt 2>&1
done

for workload in $scale_list; do
    current_concurrency=$concurrency
    if [[ $workload -lt $concurrency ]]
    then
        current_concurrency=$workload
    fi

    echo "Start scale $workload/$current_concurrency"

    mkdir -p $DIR/result/scale/nginx/$workload
    cd $DIR/result/scale/nginx/$workload
    ab -q -k -n $workload -c $current_concurrency -g basic.txt http://localhost:81/basic > basic_result.txt 2>&1
    ab -q -k -n $workload -c $current_concurrency -g hash_snefru.txt http://localhost:81/hash-snefru > hash_snefru_result.txt 2>&1

    mkdir -p $DIR/result/scale/hybrid/$workload
    cd $DIR/result/scale/hybrid/$workload
    ab -q -k -n $workload -c $current_concurrency -g basic.txt http://localhost:82/basic > basic_result.txt 2>&1
    ab -q -k -n $workload -c $current_concurrency -g hash_snefru.txt http://localhost:82/hash-snefru > hash_snefru_result.txt 2>&1

    mkdir -p $DIR/result/scale/ppm/$workload
    cd $DIR/result/scale/ppm/$workload
    ab -q -k -n $workload -c $current_concurrency -g basic.txt http://localhost:83/basic > basic_result.txt 2>&1
    ab -q -k -n $workload -c $current_concurrency -g hash_snefru.txt http://localhost:83/hash-snefru > hash_snefru_result.txt 2>&1
done

for workload in $scale_list; do
    echo "Start concurrency $workload/$workload"

    mkdir -p $DIR/result/concurrency/nginx/$workload
    cd $DIR/result/concurrency/nginx/$workload
    ab -q -k -n $workload -c $workload -g basic.txt http://localhost:81/basic > basic_result.txt 2>&1

    mkdir -p $DIR/result/concurrency/hybrid/$workload
    cd $DIR/result/concurrency/hybrid/$workload
    ab -q -k -n $workload -c $workload -g basic.txt http://localhost:82/basic > basic_result.txt 2>&1

    mkdir -p $DIR/result/concurrency/ppm/$workload
    cd $DIR/result/concurrency/ppm/$workload
    ab -q -k -n $workload -c $workload -g basic.txt http://localhost:83/basic > basic_result.txt 2>&1
done

echo 'shutdown nginx'
docker container stop $container_nginx

echo 'shutdown hibrid'
docker container stop $container_hybrid

echo 'shutdown ppm'
docker container stop $container_ppm
