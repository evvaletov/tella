import React, { useState, useEffect } from 'react';

const PrivacyCaptcha = ({ onValidated }) => {
  const [challenge, setChallenge] = useState({ question: '', answer: '' });
  const [userAnswer, setUserAnswer] = useState('');
  const [isValid, setIsValid] = useState(false);

  const generateChallenge = () => {
    const challenges = [
      {
        generate: () => {
          const num1 = Math.floor(Math.random() * 10) + 1;
          const num2 = Math.floor(Math.random() * 10) + 1;
          return {
            question: `What is ${num1} + ${num2}?`,
            answer: String(num1 + num2)
          };
        }
      },
      {
        generate: () => {
          const shapes = ['circles', 'squares', 'triangles'];
          const count = Math.floor(Math.random() * 5) + 2;
          const shape = shapes[Math.floor(Math.random() * shapes.length)];
          return {
            question: `How many ${shape} do you see?`,
            answer: String(count),
            visual: Array(count).fill(shape[0])
          };
        }
      }
    ];

    const selectedChallenge = challenges[Math.floor(Math.random() * challenges.length)];
    setChallenge(selectedChallenge.generate());
    setUserAnswer('');
    setIsValid(false);
  };

  useEffect(() => {
    generateChallenge();
  }, []);

  const handleAnswerChange = (e) => {
    const answer = e.target.value;
    setUserAnswer(answer);
    const valid = answer === challenge.answer;
    setIsValid(valid);
    onValidated(valid);
  };

  return (
    <div className="w-full p-4 bg-gray-50 rounded-md border border-gray-200">
      <div className="mb-4">
        <label className="block text-sm font-medium text-gray-700">Verification</label>
        <p className="mt-1 text-sm text-gray-500">{challenge.question}</p>
        {challenge.visual && (
          <div className="mt-2 flex gap-2">
            {challenge.visual.map((shape, index) => (
              <span key={index} className="text-2xl">
                {shape === 'c' ? '○' : shape === 's' ? '□' : '△'}
              </span>
            ))}
          </div>
        )}
      </div>
      <div className="flex gap-2">
        <input
          type="text"
          value={userAnswer}
          onChange={handleAnswerChange}
          className="w-full p-2 border rounded-md"
          placeholder="Enter your answer"
        />
        <button
          type="button"
          onClick={generateChallenge}
          className="px-4 py-2 text-sm bg-gray-200 rounded-md hover:bg-gray-300"
        >
          ↻
        </button>
      </div>
    </div>
  );
};

export default PrivacyCaptcha;
