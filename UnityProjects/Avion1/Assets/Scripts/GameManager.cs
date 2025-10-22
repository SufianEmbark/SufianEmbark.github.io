using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using TMPro;
using UnityEngine.SceneManagement;
using UnityEngine.UI;

public class GameManager : MonoBehaviour
{

    public GameObject enemyCarPrefab1;
    public GameObject enemySeagullPrefab;
    public GameObject bombLandPrefab;
    public GameObject bombPrefab;
    public GameObject bombPrefabC;
    public GameObject bombLandHelper;
    public GameObject bombLandHelperC;
    public GameObject explosionRatio;
    public GameObject explosionRatioC;
    public GameObject bum;

    private bool m_GameOver = false;
    public int finalPoints;
    //public Rigidbody bombRigid;
    public TextMeshProUGUI scoreText;
    public GameObject GameOverText;

    public bool readyToThrowBomb;
    public float score;
    public Vector3 playerPos;
    public Vector3 lBombImpactPoss;
    public Vector3 wBombImpactPoss;

    void Start()
    {
        score = 0;
        readyToThrowBomb = true;
    }

    // Update is called once per frame
    void Update()
    {
        if (Input.GetKeyDown("space"))
        {
            if (readyToThrowBomb)
            {
                readyToThrowBomb = false;

                StartCoroutine(BombCooldown());

                bombLandHelperC = Instantiate(bombLandHelper, GameObject.FindGameObjectWithTag("Player").transform.position, transform.rotation);

                bombLandHelperC.transform.parent = GameObject.FindGameObjectWithTag("Player").transform;

                lBombImpactPoss = bombLandHelperC.transform.localPosition + Vector3.forward * 21.5f;

                bombLandHelperC.transform.localPosition = lBombImpactPoss;

                wBombImpactPoss = bombLandHelperC.transform.position + Vector3.down * 26f + Vector3.forward * 8.3f;

                bombPrefabC = Instantiate(bombPrefab, GameObject.FindGameObjectWithTag("Player").transform.position, transform.rotation);

                StartCoroutine(SmoothTranslation(wBombImpactPoss, 2));
            }
        }
    }

    public void UpdateScore(float scoreToAdd)
    {
        score += scoreToAdd;
        scoreText.text = "Score: " + score;
    }

    public void GameOver()
    {
        m_GameOver = true;
        GameOverText.SetActive(true);
        CheckBestScore();
    }

    public void CheckBestScore()
    {
        if (finalPoints > MenuManager.Instance.bestScore)
        {
            Debug.Log("Puntueishon mejoreishon");//kitar

            MenuManager.Instance.bestName = MenuManager.Instance.finalName;
            MenuManager.Instance.bestScore = finalPoints;

            MenuManager.Instance.SaveBestName();
            MenuManager.Instance.SaveBestScore();

        }
    }

    IEnumerator BombCooldown()
    {
        yield return new WaitForSeconds(5);
        readyToThrowBomb = true;

    }

    IEnumerator SmoothTranslation(Vector3 target, float speed)
    {
        while (bombPrefabC.transform.position.y > target.y + 0.8)
        {
            bombPrefabC.transform.position = Vector3.Lerp(bombPrefabC.transform.position, target, Time.deltaTime * speed);
            yield return null;
        }
        Debug.Log("Bomb ase bum");
        explosionRatioC = Instantiate(explosionRatio, bombPrefabC.transform.position, transform.rotation);
        Instantiate(bum, explosionRatioC.transform.position, explosionRatioC.transform.rotation);
        StartCoroutine(ExplosionTime());
        Destroy(bombPrefabC);
    }

    IEnumerator ExplosionTime()
    {
        yield return new WaitForSeconds(0.2f);
        Destroy(explosionRatioC);
    }

}