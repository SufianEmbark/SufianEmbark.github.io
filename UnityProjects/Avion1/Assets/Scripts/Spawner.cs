using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class Spawner : MonoBehaviour
{
    public GameObject enemyPlanePrefab;
    public GameObject car;
    public GameObject carClone;
    public GameObject carContainer;

    public Vector3[] carSpawnPositions;
    public bool readyToSpawnCar;
    public int i;
    public int enemyPlaneCount;
    public int waveNumber;
    public bool readyToPlaneSpawn;
    float spawnRangeX;
    float spawnRangeZUp;
    float spawnRangeZDown;
    float spawnPosX;
    float spawnPosZ;
    public int heigh;
    public int spawnPosZChooser;
    public int spawnPosXChooser;

    public Vector3 randomPos;


    // Start is called before the first frame update
    void Start()
    {
        readyToPlaneSpawn = true;

        readyToSpawnCar = true;
        i = 1;
    }

    // Update is called once per frame
    void Update()
    {
        enemyPlaneCount = GameObject.FindGameObjectsWithTag("Enemy Plane").Length;

        if (enemyPlaneCount == 0 && readyToPlaneSpawn)
        {
            SpawnEnemyPlaneWave(waveNumber);
            waveNumber++;
            readyToPlaneSpawn = false;
            StartCoroutine(SpawnEnemyPlaneWaveCooldown());
        }

        if (readyToSpawnCar)
        {
            readyToSpawnCar = false;
            StartCoroutine(cooldownSpawnNewCar());
            Instantiate(car, carSpawnPositions[Random.Range(0, carSpawnPositions.Length)], transform.rotation);
        }


    }

    void SpawnEnemyPlaneWave(int enemiesToSpawn)
    {
        for (int i = 0; i < enemiesToSpawn; i++)
        {
            enemyPlanePrefab.GetComponent<EnemyPlaneMove>().inmunity = true;
            Instantiate(enemyPlanePrefab, GenerateAirEnemySpawnPos(), enemyPlanePrefab.transform.rotation);
            StartCoroutine(PlaneInmunityCoolDown());

        }
    }

    private Vector3 GenerateAirEnemySpawnPos()
    {
        heigh = Random.Range(1, 3);

        if (heigh == 1)
        {
            spawnRangeX = 28.0f;
            spawnRangeZUp = 35;
            spawnRangeZDown= -20;

            spawnPosX = Random.Range(-spawnRangeX, spawnRangeX);

            spawnPosZChooser = Random.Range(1, 3);

            if (spawnPosZChooser == 1)
            { spawnPosZ = spawnRangeZUp; }
            else 
            { spawnPosZ = spawnRangeZDown ; }

            Vector3 randomPosC = new Vector3(spawnPosX, 26.0f, spawnPosZ);
            randomPos = randomPosC;
        }

        else
        {
            spawnRangeX = 45f;
            spawnRangeZUp = 17.0f;
            spawnRangeZDown = -9.0f;

            spawnPosZ = Random.Range(spawnRangeZUp, spawnRangeZDown);

            spawnPosXChooser = Random.Range(1, 3);

            if (spawnPosXChooser == 1)
            { spawnPosX = spawnRangeX; }
            else
            { spawnPosX = -spawnRangeX; }
            Vector3 randomPosC = new Vector3(spawnPosX, 26.0f, spawnPosZ);
            randomPos = randomPosC;
        }

        
        return randomPos;
    }
    IEnumerator PlaneInmunityCoolDown()
    {
        yield return new WaitForSeconds(1);
        enemyPlanePrefab.GetComponent<EnemyPlaneMove>().inmunity = false;

    }
    IEnumerator SpawnEnemyPlaneWaveCooldown()
    {
        yield return new WaitForSeconds(30);
        readyToPlaneSpawn = true;

    }
    IEnumerator cooldownSpawnNewCar()
    {
        yield return new WaitForSeconds(Random.Range(3, 10));
        readyToSpawnCar = true;
    }
}
