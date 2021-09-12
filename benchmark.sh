DIR=$(PWD)
docker run --rm -d -p 81:80/tcp benchmark:nginx
docker run --rm -d -p 82:80/tcp benchmark:hybrid
docker run --rm -d -p 83:80/tcp benchmark:ppm

load_list="10 20 40 80"

for wkload in load_list; do
    mkdir -p cd $DIR/result/workload/nginx/$wkload
    cd $DIR/result/workload/nginx/$wkload

    echo "Start nginx $wkload/5"
    ab -k -n $wkload -c 5 -g basic.txt http://localhost:81/basic > basic_result.txt 2>&1
    ab -k -n $wkload -c 5 -g hash_snefru.txt http://localhost:81/hash-snefru > hash_snefru_result.txt 2>&1
    ab -k -n $wkload -c 5 -g hash_ghost.txt http://localhost:81/hash-ghost > hash_ghost_result.txt 2>&1
    # ab -k -n $wkload -c 5 -g sleep.txt http://localhost:81/sleep > sleep_result.txt 2>&1
    ab -k -n $wkload -c 5 -g stream.txt http://localhost:81/stream > stream_result.txt 2>&1
    ab -k -n $wkload -c 5 -g random.txt http://localhost:81/random > random_result.txt 2>&1
    ab -k -n $wkload -c 5 -g big_stream.txt http://localhost:81/big-stream > big_stream_result.txt 2>&1
done

for wkload in load_list; do
    mkdir -p cd $DIR/result/workload/hybrid/$wkload
    cd $DIR/result/workload/hybrid/$wkload

    echo "Start hybrid $wkload/5"
    ab -k -n $wkload -c 5 -g basic.txt http://localhost:82/basic > basic_result.txt 2>&1
    ab -k -n $wkload -c 5 -g hash_snefru.txt http://localhost:82/hash-snefru > hash_snefru_result.txt 2>&1
    ab -k -n $wkload -c 5 -g hash_ghost.txt http://localhost:82/hash-ghost > hash_ghost_result.txt 2>&1
    # ab -k -n $wkload -c 5 -g sleep.txt http://localhost:82/sleep > sleep_result.txt 2>&1
    ab -k -n $wkload -c 5 -g stream.txt http://localhost:82/stream > stream_result.txt 2>&1
    ab -k -n $wkload -c 5 -g random.txt http://localhost:82/random > random_result.txt 2>&1
    ab -k -n $wkload -c 5 -g big_stream.txt http://localhost:82/big-stream > big_stream_result.txt 2>&1
done

for wkload in load_list; do
    mkdir -p cd $DIR/result/workload/ppm/$wkload
    cd $DIR/result/workload/ppm/$wkload

    echo "Start ppm $wkload/5"
    ab -k -n $wkload -c 5 -g basic.txt http://localhost:83/basic > basic_result.txt 2>&1
    ab -k -n $wkload -c 5 -g hash_snefru.txt http://localhost:83/hash-snefru > hash_snefru_result.txt 2>&1
    ab -k -n $wkload -c 5 -g hash_ghost.txt http://localhost:83/hash-ghost > hash_ghost_result.txt 2>&1
    # ab -k -n $wkload -c 5 -g sleep.txt http://localhost:83/sleep > sleep_result.txt 2>&1
    ab -k -n $wkload -c 5 -g stream.txt http://localhost:83/stream > stream_result.txt 2>&1
    ab -k -n $wkload -c 5 -g random.txt http://localhost:83/random > random_result.txt 2>&1
    ab -k -n $wkload -c 5 -g big_stream.txt http://localhost:83/big-stream > big_stream_result.txt 2>&1
done


load_list="1 2 4 8 16 32 64 128 256 512 1024"
for wkload in load_list; do
    mkdir -p cd $DIR/result/scale/nginx/$wkload
    cd $DIR/result/scale/nginx/$wkload

    echo "Start scale nginx $wkload/5"
    ab -k -n $wkload -c 5 -g basic.txt http://localhost:81/basic > basic_result.txt 2>&1
    ab -k -n $wkload -c 5 -g hash_snefru.txt http://localhost:81/hash-snefru > hash_snefru_result.txt 2>&1

    mkdir -p cd $DIR/result/scale/hybrid/$wkload
    cd $DIR/result/scale/hybrid/$wkload

    echo "Start scale hybrid $wkload/5"
    ab -k -n $wkload -c 5 -g basic.txt http://localhost:82/basic > basic_result.txt 2>&1
    ab -k -n $wkload -c 5 -g hash_snefru.txt http://localhost:82/hash-snefru > hash_snefru_result.txt 2>&1

    mkdir -p cd $DIR/result/scale/ppm/$wkload
    cd $DIR/result/scale/ppm/$wkload

    echo "Start scale ppm $wkload/5"
    ab -k -n $wkload -c 5 -g basic.txt http://localhost:83/basic > basic_result.txt 2>&1
    ab -k -n $wkload -c 5 -g hash_snefru.txt http://localhost:83/hash-snefru > hash_snefru_result.txt 2>&1
done
